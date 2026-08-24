package Space_Invaders.Controller.Menu;

import Space_Invaders.Controller.Controller;
import Space_Invaders.Controller.Sound.SoundManager;
import Space_Invaders.Game;
import Space_Invaders.Model.Menu.GameOverMenu;
import Space_Invaders.Model.Sound.Sound;
import Space_Invaders.Model.Sound.Sound_Options;
import Space_Invaders.State.GameState;
import com.googlecode.lanterna.input.KeyStroke;

import java.io.IOException;


public class GameOverMenuController extends Controller<GameOverMenu> {

    public GameOverMenuController(GameOverMenu menu) {
        super(menu);
        SoundManager.getInstance().playSound(Sound_Options.GAME_OVER);
    }

    @Override
    public void step(Game game, KeyStroke key, long time) throws IOException {
        if(key == null){
            return;
        }
        switch (key.getKeyType()) {
            case ArrowUp:
                getModel().previous();
                SoundManager.getInstance().playSound(Sound_Options.MENU_SWITCH);

                break;
            case ArrowDown:
                getModel().next();
                SoundManager.getInstance().playSound(Sound_Options.MENU_SWITCH);
                break;
            case Enter:
                if (getModel().isSelectedTryAgain()) {
                    game.setState(GameState.NEW_GAME);
                } else if (getModel().isSelectedInstructions()) {
                    game.setState(GameState.INSTRUCTIONS);
                } else if (getModel().isSelectedScoreboard()) {
                    game.setState(GameState.SCOREBOARD);
                } else if (getModel().isSelectedTheme()) {
                    game.setState(GameState.THEME);
                } else if (getModel().isSelectedExit()) {
                    game.setState(GameState.START);
                }
                else if(getModel().isSelectedSoundOptions()){
                    game.setState(GameState.SOUND_OPTIONS);
                }
                SoundManager.getInstance().playSound(Sound_Options.ENTER);
                break;
            case Escape:
                game.setState(GameState.START);
                SoundManager.getInstance().playSound(Sound_Options.ENTER);
                break;
            default: break;
        }
    }


}


