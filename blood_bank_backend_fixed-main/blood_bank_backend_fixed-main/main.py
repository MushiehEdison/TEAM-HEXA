from fastapi import FastAPI, Depends, HTTPException, UploadFile, File
from sqlalchemy.orm import Session
from sqlalchemy import func
import pandas as pd
from database import engine, SessionLocal
import models, schemas, crud
from io import StringIO
from fastapi import APIRouter
from sqlalchemy import func, cast, Date

from fastapi.middleware.cors import CORSMiddleware

app = FastAPI()

# ✅ CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"], 
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# ✅ Create the tables
models.Base.metadata.create_all(bind=engine)

# ✅ Dependency for DB session
def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

@app.get("/")
def read_root():
    return {"message": "Blood Bank API is running"}

@app.get("/donations", response_model=list[schemas.BloodRecord])
def get_donations(limit: int = 100, db: Session = Depends(get_db)):
    return db.query(models.BloodRecord).limit(limit).all()

@app.post("/upload_csv")
async def upload_csv(file: UploadFile = File(...), db: Session = Depends(get_db)):
    try:
        contents = await file.read()
        df = pd.read_csv(StringIO(contents.decode('utf-8')))
        crud.bulk_insert_donations(db, df)
        return {"message": "CSV uploaded and data inserted successfully"}
    except Exception as e:
        raise HTTPException(status_code=400, detail=f"Upload failed: {str(e)}")

@app.get("/chart/blood-types")
def blood_type_distribution(db: Session = Depends(get_db)):
    results = (
        db.query(models.BloodRecord.blood_type, func.count().label("count"))
        .group_by(models.BloodRecord.blood_type)
        .all()
    )
    return [{"label": r[0], "value": r[1]} for r in results]

@app.get("/chart/stock-over-time")
def blood_stock_over_time(db: Session = Depends(get_db)):
    results = (
        db.query(
            cast(models.BloodRecord.donation_date, Date).label("date"),
            func.count().label("count")
        )
        .group_by(cast(models.BloodRecord.donation_date, Date))
        .order_by(cast(models.BloodRecord.donation_date, Date))
        .all()
    )
    return [{"date": str(r[0]), "count": r[1]} for r in results]
