const metaCode = document.createElement('meta');

metaCode.name = 'prerender-status-code';
metaCode.setAttribute('content', '404');
document.getElementsByTagName('head')[0].appendChild(metaCode);

const NotFound = () => {
  return (
    <div className='not-found'>
      <h1>Page not found</h1>
    </div>
  );
}
  
export default NotFound;