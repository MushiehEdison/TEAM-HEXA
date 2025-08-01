from fastapi import APIRouter, UploadFile, File, HTTPException, Depends
import pandas as pd
from sqlalchemy.orm import Session
from database import get_db
from crud import bulk_insert_donations
from io import StringIO

router = APIRouter()

@router.post("/upload_csv")
async def upload_csv(file: UploadFile = File(...), db: Session = Depends(get_db)):
    try:
        contents = await file.read()
        df = pd.read_csv(StringIO(contents.decode('utf-8')))

        if df.empty:
            raise HTTPException(status_code=400, detail="Uploaded file is empty.")

        bulk_insert_donations(db, df)
        return {"message": f"Successfully uploaded {len(df)} records."}

    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Upload failed: {str(e)}")
