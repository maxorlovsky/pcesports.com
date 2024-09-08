// 3rd party libs
import { useEffect } from 'react';

const NotFound = () => {
  useEffect(() => {
    const metaCode = document.createElement('meta');

    metaCode.name = 'prerender-status-code';
    metaCode.setAttribute('content', '404');
    document.getElementsByTagName('head')[0].appendChild(metaCode);

    // Returning a cleanup function
    return () => {
      // Remove metaCode
      document.querySelector('meta[name="prerender-status-code"]')?.remove();
    };
  }, []);

  return (
    <div className='not-found'>
      <h1>Page not found</h1>
    </div>
  );
}
  
export default NotFound;