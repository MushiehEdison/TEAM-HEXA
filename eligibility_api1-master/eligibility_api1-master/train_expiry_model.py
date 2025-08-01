import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.pipeline import Pipeline
from sklearn.preprocessing import FunctionTransformer
from sklearn.metrics import mean_absolute_error
import joblib

# Load your dataset CSV here - replace with your actual file path
df = pd.read_csv("cleaned_blood_data.csv")

# Convert donation_date to datetime
df["donation_date"] = pd.to_datetime(df["donation_date"], errors='coerce')

# Drop rows with missing essential data
df = df.dropna(subset=[
    "donation_date", "blood_type", "collection_volume_ml", 
    "hemoglobin_g_dl", "donor_age", "shelf_life_days"
])

# Map blood_type to numeric (simple mapping)
blood_type_map = {"A": 1, "B": 2, "AB": 3, "O": 4}
df["blood_type_encoded"] = df["blood_type"].map(blood_type_map).fillna(0).astype(int)

# Extract day and month from donation_date
df["donation_day"] = df["donation_date"].dt.day
df["donation_month"] = df["donation_date"].dt.month

# Features and target
features = [
    "donation_day",
    "donation_month",
    "blood_type_encoded",
    "collection_volume_ml",
    "hemoglobin_g_dl",
    "donor_age"
]
X = df[features]
y = df["shelf_life_days"]

# Train/test split
X_train, X_val, y_train, y_val = train_test_split(X, y, test_size=0.2, random_state=42)

# Simple model pipeline (no heavy transformers)
model = RandomForestRegressor(n_estimators=50, random_state=42)

model.fit(X_train, y_train)
val_preds = model.predict(X_val)
mae = mean_absolute_error(y_val, val_preds)
print(f"Validation MAE: {mae:.2f} days")

# Save model
joblib.dump(model, "expiry_predictor.pkl")
print("Model saved as expiry_predictor.pkl")
