from sqlalchemy import Column, Integer, String, Float, Date
from database import Base

class BloodDonation(Base):
    __tablename__ = "blood_donations"

    id = Column(Integer, primary_key=True, index=True)
    donation_date = Column(Date)
    blood_type = Column(String)
    collection_volume_ml = Column(Float, nullable=True) 
    hemoglobin_g_dl = Column(Float) 
    donor_age = Column(Integer)
    donor_gender = Column(String)
    shelf_life_days = Column(Integer)
    
