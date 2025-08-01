from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
import joblib
import numpy as np

router = APIRouter()

# Load the trained model
model = joblib.load("expiry_predictor.pkl")

# Pydantic model matching the 6 training features
class ExpiryInput(BaseModel):
    donation_day: int
    donation_month: int
    blood_type: str
    collection_volume_ml: float
    hemoglobin_g_dl: float
    donor_age: int

# Blood type map used during training
blood_type_map = {"A": 1, "B": 2, "AB": 3, "O": 4}

@router.post("/predict_expiry")
def predict_expiry(data: ExpiryInput):
    try:
        # Encode blood type
        blood_type_encoded = blood_type_map.get(data.blood_type.upper(), 0)

        # Build feature array
        features = np.array([
            data.donation_day,
            data.donation_month,
            blood_type_encoded,
            data.collection_volume_ml,
            data.hemoglobin_g_dl,
            data.donor_age
        ]).reshape(1, -1)

        prediction = model.predict(features)
        return {"predicted_shelf_life_days": float(prediction[0])}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
