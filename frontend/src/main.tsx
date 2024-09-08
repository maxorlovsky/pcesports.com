
// 3rd party libs
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';

// Components
import Header from './components/header';
import Footer from './components/footer';
import App from './App.tsx';
import './styles/index.scss';

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <BrowserRouter>
      <Header />
      <div className="body-content">
        <App />
      </div>
      <Footer />
    </BrowserRouter>
  </StrictMode>,
)
