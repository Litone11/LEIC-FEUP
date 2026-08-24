package Space_Invaders.Model.Menu;

import java.lang.reflect.Array;
import java.util.Arrays;
import java.util.List;

public class InstructionsMenu extends Menu {
    public InstructionsMenu() {options = Arrays.asList("exit");}

    public boolean isSelectedExit(){return isSelected(0);}
}

