from sqlalchemy.orm import Session
import pandas as pd
from models import BloodRecord  
from datetime import datetime

def bulk_insert_donations(db: Session, df: pd.DataFrame):
    for _, row in df.iterrows():
        try:
            donation = BloodRecord(
                record_id=row['record_id'],
                donor_id=row['donor_id'],
                donor_age=row['donor_age'],
                donor_gender=row['donor_gender'],
                blood_type=row['blood_type'],
                collection_site=row['collection_site'],
                donation_date=pd.to_datetime(row['donation_date']).date(),
                expiry_date=pd.to_datetime(row['expiry_date']).date(),
                collection_volume_ml=row['collection_volume_ml'],
                hemoglobin_g_dl=row['hemoglobin_g_dl'],
                shelf_life_days=row['shelf_life_days'],
                will_expire_early=row['will_expire_early']
            )
            db.merge(donation)
            db.commit()  
        except Exception as e:
            print(f"❌ Skipping record {row['record_id']} due to error: {e}")
            db.rollback()  
