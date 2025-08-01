from pydantic import BaseModel
from datetime import date
from typing import Optional


class BloodRecord(BaseModel):
    record_id: str
    donor_id: str
    donor_age: Optional[float] = None
    donor_gender: Optional[str] = None
    blood_type: Optional[str] = None
    collection_site: Optional[str] = None
    donation_date: date
    expiry_date: Optional[date] = None
    collection_volume_ml: Optional[float] = None
    hemoglobin_g_dl: Optional[float] = None
    shelf_life_days: Optional[int] = None
    will_expire_early: Optional[bool] = None

    class Config:
        orm_mode = True
