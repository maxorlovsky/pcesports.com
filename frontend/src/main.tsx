
// 3rd party libs
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';

// Components
import Header from './components/header';
import Footer from './components/footer';
import Loading from './components/loading';
import App from './App.tsx';
import './styles/index.scss';

// Images
import bgImage from '../src/assets/bg.jpg';


createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <BrowserRouter>
      <Header />
      <div className="body-content"
        style={{backgroundImage: `url("${bgImage}")`}}
      >
        <App />
        <Loading />
      </div>
      <Footer />
    </BrowserRouter>
  </StrictMode>,
)