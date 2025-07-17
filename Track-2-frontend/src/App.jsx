import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import EditProfile from './components/profile';
import History from './components/history';
import AllVisits from './components/visits';
import Home from './home';

const App = () => {


  return (
    <Router>
        <main className="pt-16 pb-20">
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/profile" element={<EditProfile />} />
            <Route path="/profile/edit" element={<EditProfile isEditing={true} />} />
            <Route path="/visits" element={<AllVisits />} />
            <Route path="/history" element={<History />} />
          </Routes>
        </main>
    </Router>
  );
};

export default App;