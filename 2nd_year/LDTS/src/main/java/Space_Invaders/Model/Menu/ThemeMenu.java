package Space_Invaders.Model.Menu;

import java.util.Arrays;

public class ThemeMenu extends Menu {

    public ThemeMenu() {
        super();
        options = Arrays.asList("space", "pacman", "exit");
    }

    // Public methods to check if specific themes are selected
    public boolean isSelectedSpace() {
        return isSelected(0);
    }

    public boolean isSelectedPacMan() {
        return isSelected(1);
    }


    public boolean isSelectedExit() {return isSelected(2);}
}
