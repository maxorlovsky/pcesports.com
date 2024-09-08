// SCSS
import './styles.scss';

const Home = () => {
  return (
    <div className='home'>
      <h1>List of resources to watch major events</h1>

      <div>PCEsports started over a decade ago with the mission to address a challenge that has since evolved. Today, it serves as a streamlined hub, offering quick and direct links to access major live <a href="https://en.wikipedia.org/wiki/Esports" target="_blank">esports</a> events and content. If you know of any major events or platforms that provide efficient access to esports streams and aren't yet listed, please connect with me on <a href="https://discord.com/channels/@me/165812964878712832" target="_blank">Discord</a> to help us keep PCEsports up-to-date and comprehensive!</div>

      <h2>Counter-Strike 2</h2>
      <ul>
        <li>
          <a href="https://www.hltv.org/events" target="_blank">HLTV</a>
        </li>
        <li>
          <a href="https://theesportslab.com/esports/cs2/home" target="_blank">The Esports Lab</a>
        </li>
      </ul>

      <h2>League of Legends</h2>
      <ul>
        <li>
          <a href="https://lolesports.com/en-US" target="_blank">LoL Esports</a>
        </li>
        <li>
          <a href="https://theesportslab.com/esports/lol/home" target="_blank">The Esports Lab</a>
        </li>
        
      </ul>

      <h2>Dota 2</h2>
      <ul>
        <li>
          <a href="https://www.dota2.com/esports" target="_blank">Dota 2 Esports</a>
        </li>
        <li>
          <a href="https://theesportslab.com/esports/dota2/home" target="_blank">The Esports Lab</a>
        </li>
      </ul>
    </div>
  );
}
  
export default Home;