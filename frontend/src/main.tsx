
// 3rd party libs
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Route, Routes } from 'react-router-dom';

// Components
import Header from './components/header';
import Footer from './components/footer';

// Pages
import Home from './pages/home';
import NotFound from './pages/not-found';

// SCSS
import './styles/index.scss';

// Images
import bgImage from '../src/assets/bg.jpg';
import faviconImage from '../src/assets/favicon.ico';

// Update body's background
document.body.style.backgroundImage = `url("${bgImage}")`;

// Add favicon
const favicon = document.createElement('link');

favicon.rel = 'shortcut icon';
favicon.href = faviconImage;

document.getElementsByTagName('head')[0].appendChild(favicon);

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <BrowserRouter>
      <Header />
      <div className="body-content">
        <div className="container">
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="*" element={<NotFound />} />
          </Routes>
        </div>
      </div>

      <div className="body-fader" />
      <Footer />
    </BrowserRouter>
  </StrictMode>,
)