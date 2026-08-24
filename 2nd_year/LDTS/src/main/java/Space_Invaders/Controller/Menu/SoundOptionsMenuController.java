package Space_Invaders.Controller.Menu;

import Space_Invaders.Controller.Controller;
import Space_Invaders.Controller.Sound.SoundManager;
import Space_Invaders.Game;
import Space_Invaders.Model.Menu.SoundOptionsMenu;
import Space_Invaders.Model.Sound.Sound_Options;
import com.googlecode.lanterna.input.KeyStroke;

import java.io.IOException;

public class SoundOptionsMenuController extends Controller<SoundOptionsMenu> {

    public SoundOptionsMenuController(SoundOptionsMenu soundOptionsMenu) { super(soundOptionsMenu); }

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
                if(getModel().isSelectedMuteAll()){
                    SoundManager.getInstance().setMute(!SoundManager.getInstance().isMute());
                }
                else if(getModel().isSelectedBackgroundMusic()){
                    SoundManager.getInstance().setMusic(!SoundManager.getInstance().isMusic());
                }
                else if(getModel().isSelectedSoundEffects()){
                    SoundManager.getInstance().setEffects(!SoundManager.getInstance().isEffects());

                }
                else if(getModel().isSelectedExit()){
                    game.setPreviousState();
                }
                SoundManager.getInstance().updateMute(!SoundManager.getInstance().isMusic() && !SoundManager.getInstance().isEffects());
                SoundManager.getInstance().playSound(Sound_Options.ENTER);
                getModel().updateOptions();
                break;
            case Escape:
                game.setPreviousState();
                SoundManager.getInstance().playSound(Sound_Options.ENTER);
                break;
            default:
        }
    }
}
