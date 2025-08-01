from sqlalchemy import Column, String, Float, Integer, Date
from database import Base

class BloodRecord(Base):
    __tablename__ = "blood_records"
    record_id = Column(String, primary_key=True, index=True)
    donor_id = Column(String)
    donor_age = Column(Float)
    donor_gender = Column(String)
    blood_type = Column(String)
    collection_site = Column(String)
    donation_date = Column(Date)
    expiry_date = Column(Date)
    collection_volume_ml = Column(Float)
    hemoglobin_g_dl = Column(Float)
    shelf_life_days = Column(Integer)
    will_expire_early = Column(Integer)
