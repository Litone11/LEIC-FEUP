package Space_Invaders.Model.Menu;

import java.util.Arrays;

public class ScoreboardMenu extends Menu{
    public ScoreboardMenu() {options = Arrays.asList("exit");};

    public boolean isSelectedExit(){return isSelected(0);}
}
