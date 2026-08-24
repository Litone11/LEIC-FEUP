package Space_Invaders.Controller.Menu;

import Space_Invaders.Controller.Controller;
import Space_Invaders.Controller.Sound.SoundManager;
import Space_Invaders.Game;
import Space_Invaders.Model.Menu.ThemeMenu;
import Space_Invaders.Model.Sound.Sound_Options;
import Space_Invaders.State.ThemeState;
import com.googlecode.lanterna.input.KeyStroke;

import java.io.IOException;

public class ThemeMenuController extends Controller<ThemeMenu> {
    public ThemeMenuController(ThemeMenu menu){
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
                if (getModel().isSelectedExit()){game.setPreviousState();}
                else if(getModel().isSelectedSpace()){game.setTheme(ThemeState.SPACE);game.setPreviousState();}
                else if(getModel().isSelectedPacMan()){game.setTheme(ThemeState.PACMAN);game.setPreviousState();}
                SoundManager.getInstance().playSound(Sound_Options.ENTER);
                break;
            case Escape:
                game.setPreviousState();
                SoundManager.getInstance().playSound(Sound_Options.ENTER);
                break;
            default:
        }
    }
}
