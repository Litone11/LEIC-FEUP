package Space_Invaders.Controller.Menu;

import Space_Invaders.Controller.Controller;
import Space_Invaders.Controller.Sound.SoundManager;
import Space_Invaders.Game;
import Space_Invaders.Model.Menu.PauseMenu;
import Space_Invaders.Model.Sound.Sound_Options;
import Space_Invaders.State.GameState;
import com.googlecode.lanterna.input.KeyStroke;

import java.io.IOException;

public class PauseMenuController extends Controller<PauseMenu> {

    public PauseMenuController(PauseMenu menu){
        super(menu);
    }

    @Override
    public void step(Game game, KeyStroke key, long time) throws IOException {
        if(key == null){
            return;
        }
        switch(key.getKeyType()){
            case ArrowUp:
                getModel().previous();
                SoundManager.getInstance().playSound(Sound_Options.MENU_SWITCH);
                break;
            case ArrowDown:
                getModel().next();
                SoundManager.getInstance().playSound(Sound_Options.MENU_SWITCH);

                break;
            case Enter:
                if(getModel().isSelectedContinue()){
                    game.setState(GameState.RESUME_GAME);
                }
                else if(getModel().isSelectedInstructions()){
                    game.setState(GameState.INSTRUCTIONS);
                }
                else if(getModel().isSelectedRestart()){
                    game.setState(GameState.NEW_GAME);
                }
                else if(getModel().isSelectedExit()){
                    game.setState(GameState.START);
                }
                else if(getModel().isSelectedScoreboard()){
                    game.setState(GameState.SCOREBOARD);
                }
                else if(getModel().isSelectedTheme()){
                    game.setState(GameState.THEME);
                }
                else if(getModel().isSelectedSoundOptions()){
                    game.setState(GameState.SOUND_OPTIONS);
                }
                SoundManager.getInstance().playSound(Sound_Options.ENTER);
                break;
            case Escape:
                SoundManager.getInstance().playSound(Sound_Options.ENTER);
                game.setPreviousState();
                break;
            default:
        }
    }
}
