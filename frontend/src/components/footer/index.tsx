// SCSS
import './styles.scss';

function Footer() {
  const year = new Date().getFullYear();

  return (
    <footer>
      <p className="copyrights">
        © 2014 - {year} PCEsports.com.
      </p>

      <div className="footer-links">
        <a href="https://twitter.com/pcesports" target="_blank" rel="noreferrer noopener"><i className="icon icon-twitter"></i></a>
      </div>
    </footer>
  );
}
  
export default Footer;