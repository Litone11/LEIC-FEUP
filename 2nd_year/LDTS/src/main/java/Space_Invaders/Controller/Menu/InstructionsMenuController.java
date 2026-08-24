package Space_Invaders.Controller.Menu;

import Space_Invaders.Controller.Controller;
import Space_Invaders.Controller.Sound.SoundManager;
import Space_Invaders.Game;
import Space_Invaders.Model.Menu.InstructionsMenu;
import Space_Invaders.Model.Sound.Sound_Options;
import Space_Invaders.State.GameState;
import com.googlecode.lanterna.input.KeyStroke;

import java.io.IOException;

public class InstructionsMenuController extends Controller<InstructionsMenu> {
    public InstructionsMenuController(InstructionsMenu menu) {super(menu);}

    @Override
    public void step(Game game, KeyStroke key, long time) throws IOException {
        if(key == null){return;}

        switch(key.getKeyType()){
            case Enter, Escape:
                game.setPreviousState();
                SoundManager.getInstance().playSound(Sound_Options.ENTER);
                break;
            default:
        }
    }

}
