// Images
import loadingImage from '../../assets/loading.svg';

// SCSS
import './styles.scss';

const Loading = () => {
  return (
    <div className="loading">
      <img src={loadingImage}
        alt="Loading"
      />
    </div>
  );
}
  
export default Loading;