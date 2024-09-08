// 3rd party libs
import { Link } from 'react-router-dom';
import { ReactElement, useState } from 'react';

// Images
import logo from '../../assets/logo.png';

// Interfaces
import { LinkInterface } from './interface';

// SCSS
import './styles.scss';

function Header() {
  const [logoSmall, setLogoSmall] = useState<boolean>(false);

  const menu: LinkInterface[] = [
    {
      title: 'Home',
      url: '/',
    }
  ];

  // To handle different display for logo when scrolling down
  const handleScroll = (): void => {
    if (window.scrollY !== 0) {
      setLogoSmall(true);
    }
    else {
      setLogoSmall(false);
    }
  };

  window.addEventListener('scroll', handleScroll);

  return (
    <header>
      <Link to="/">
        <div className={`logo ${logoSmall ? 'logo-small' : null}`}>
          <img src={logo}
            alt="PCEsports.com"
          />
        </div>
      </Link>

      <nav>
        <ul>
          {
            menu.map((link: LinkInterface) => (
              <li key={link.url}
                className="nav-link"
              >
                {navItemTemplate(link)}
              </li>
            ))
          }
        </ul>
      </nav>
    </header>
  );
}

function navItemTemplate(link: LinkInterface): ReactElement {
  return (
    <Link to={link.url}>
      {link.title}
    </Link>
  );
}
  
export default Header;