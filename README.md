# 🧠 Dr. Healia – AI Medical Assistant (Track 2)

Welcome to *Dr. Healia*, an AI-powered healthcare assistant built to support patients in expressing symptoms and receiving empathetic, multilingual medical support. This project was developed as part of **Track 2 of the CODE2CARE Hackathon**.

---

## 🚀 Project Overview

*Dr. Healia* is a comprehensive healthcare platform that bridges the gap between patients and medical professionals through intelligent AI assistance. The system provides personalized medical guidance while maintaining data privacy and security.

### Key Capabilities
- 🤖 AI-patient conversations in English 🇺🇸 and French 🇫🇷
- 🧠 Advanced emotion and symptom tracking
- 📊 Medical dataset integration for evidence-based recommendations
- 🔐 Secure role-based authentication system
- 🌍 Geolocation-based health insights
- 📱 Responsive design for mobile and desktop

---

## 🔐 Features

### 👩‍⚕️ For Patients
- *Natural Language Processing*: Engage in natural conversations with AI doctor
- *Multilingual Support*: Communicate in English or French
- *Symptom Tracking*: Automatic detection and logging of medical symptoms
- *Emotional Analysis*: Monitor emotional well-being throughout treatment
- *Personalized Recommendations*: AI-driven health advice based on conversation history
- *Privacy Protection*: Secure handling of sensitive medical information
- *Conversation History*: Access to previous consultations and recommendations

### 🛠️ For Administrators
- Secure Authentication: Role-based access control system
- Health Intelligence Dashboard featuring:
  - ✅ **Symptom Trends Analysis** (Interactive bar charts)
  - ✅ **Emotional State Distribution** (Dynamic pie charts)
  - ✅ **Top Medical Conditions Table** (Ranked condition frequency)
  - ✅ **Geographic Heatmap** (Symptom hotspot visualization)
  - ✅ **Real-time Alerts** (Spike detection for epidemiological monitoring)
  - ✅ **Patient Sentiment Progression** (Emotional journey tracking)
- **Export Capabilities**: Download reports and analytics
- **User Management**: Monitor patient interactions and system usage

---

## 🛠️ Tech Stack

| Layer | Technology | Purpose |
|-------|------------|---------|
| *Frontend* | React.js + Tailwind CSS | Modern, responsive user interface |
| *Backend* | Flask (Python) | RESTful API and business logic |
| *AI Engine* | Groq API (LLM) | Natural language processing and medical reasoning |
| *Database* | SQLite / Postgre | Patient data and conversation storage |
| *Authentication* | JWT / Session-based | Secure user authentication |
| *Deployment* | Render / Netlify | Cloud hosting and continuous deployment |
| *Analytics* | Chart.js / D3.js | Data visualization components |

---

## 📂 Project Structure

```
dr-healia/
├── backend/
│   ├── app.py                 # Main Flask application
│   ├── models/
│   │   ├── user.py           # User model and authentication
│   │   ├── conversation.py   # Conversation data model
│   │   └── analytics.py      # Analytics and reporting models
│   ├── routes/
│   │   ├── auth.py          # Authentication endpoints
│   │   ├── chat.py          # Chat and AI interaction endpoints
│   │   └── admin.py         # Admin dashboard endpoints
│   │   └── analytics_service.py # Data analysis and insights
│   ├── requirements.txt     # Python dependencies
│   └── .env.example        # Environment variables template
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   │   ├── Chat/        # Chat interface components
│   │   │   ├── Dashboard/   # Admin dashboard components
│   │   │   └── Auth/        # Authentication components
│   │   ├
│   │   ├
│   │   │  
│   │   └── App.js           # Main React application
│   ├── public/
│   └── package.json         # Node.js dependencies
└── README.md              # This file
```

---

## 🧠 System Architecture

### Data Flow
1. Patient Interaction: User initiates conversation through React frontend
2. Language Detection: System automatically detects user's preferred language
3. AI Processing: Groq API processes natural language and generates medical responses
4. Data Extraction: NLP service extracts:
   - Symptoms and medical conditions
   - Emotional indicators
   - Severity assessments
5. Database Storage: Structured data stored for analytics and future reference
6. Real-time Analytics: Admin dashboard updates with new insights


### Security Features
- Data Encryption: All sensitive data encrypted at rest and in transit
- Role-Based Access: Separate interfaces for patients and administrators
- Privacy Compliance: HIPAA-aligned data handling practices
- Audit Logging: Comprehensive logging for security monitoring

---

## 🚧 Installation & Setup

### Prerequisites
- Python 3.8+
- Node.js 14+
- Git

### Backend Setup

```bash
# Clone the repository
git clone https://github.com/MushiehEdison/backend.git
cd backend

# Create and activate virtual environment
python -m venv venv

# On Windows
venv\Scripts\activate
# On macOS/Linux
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt

# Setup environment variables
cp .env.example .env
# Edit .env file with your configurations:
# GROQ_API_KEY=your_groq_api_key
# DATABASE_URL=your_database_url
# SECRET_KEY=your_secret_key
# FLASK_ENV=development

# Initialize database
python -c "from app import db; db.create_all()"

# Run the application
python app.py
```

### Frontend Setup

```bash
# Navigate to frontend directory
cd frontend

# Install dependencies
npm install

# Setup environment variables
cp .env.example .env.local
# Add your API endpoints and keys

# Start development server
npm start
```

### Environment Variables

Create a `.env` file in the backend directory:

```env
# Groq API Configuration
GROQ_API_KEY=your_groq_api_key_here

# Database Configuration
DATABASE_URL=sqlite:///healia.db
# For Postgre: postgre://username:password@localhost/healia

# Flask Configuration
SECRET_KEY=your_secret_key_here
FLASK_ENV=development
FLASK_DEBUG=True

# Security Settings
JWT_SECRET_KEY=your_jwt_secret
SESSION_PERMANENT=False

# External Services
GOOGLE_MAPS_API_KEY=your_google_maps_key (optional)
```

---

## 🔧 API Documentation

### Authentication Endpoints
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `POST /api/auth/logout` - User logout

### Chat Endpoints
- `POST /api/chat/message` - Send message to AI
- `GET /api/chat/history` - Get conversation history
- `DELETE /api/chat/clear` - Clear conversation history

### Admin Endpoints
- `GET /api/admin/dashboard` - Dashboard data
- `GET /api/admin/analytics` - Detailed analytics
- `GET /api/admin/users` - User management
- `POST /api/admin/alerts` - Configure alerts

---

## 📊 Analytics & Insights

Dr. Healia provides comprehensive health analytics including:

- Epidemiological Tracking: Monitor disease patterns and outbreaks
- Sentiment Analysis: Track patient emotional well-being over time
- Geographic Distribution: Visualize health trends by location
- Demographic Insights: Understand health patterns across age groups
- *Predictive Analytics: Early warning systems for health emergencies

---

🧪 Testing

```bash
# Backend tests
cd backend
python -m pytest tests/

# Frontend tests
cd frontend
npm test

# Run all tests
npm run test:all
```

---

🚀 Deployment

Using Render (Backend)
1. Connect your GitHub repository to Render
2. Set environment variables in Render dashboard
3. Deploy with automatic builds
4. https://github.com/MushiehEdison/backend/

Using Netlify (Frontend)
1. Connect repository to Vercel
2. Configure build settings
3. Deploy with automatic updates
4. https://github.com/MushiehEdison/frontend/

---

🙏 Acknowledgments

- CODE2CARE Hackathon organizers and participants
- Groq AI for powerful language processing capabilities
- Open source community for tools and libraries
- Medical professionals who provided domain expertise

---

🔮 Roadmap

Phase 1 (Current)
- ✅ Basic AI chat functionality
- ✅ Multilingual support (EN/FR)
- ✅ Admin dashboard
- ✅ Symptom tracking

 Phase 2 (Upcoming)
- 🔄 Mobile app development
- 🔄 Integration with electronic health records
- 🔄 Telemedicine video consultations
- 🔄 Prescription management

 Phase 3 (Future)
- 🔮 IoT device integration
- 🔮 Advanced ML diagnostics
- 🔮 Multi-clinic deployment
- 🔮 Research partnerships


Made with ❤️ for better healthcare accessibility
