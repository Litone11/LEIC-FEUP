package Space_Invaders.Model.Menu;

import java.util.Arrays;

public class PauseMenu extends Menu{
    public PauseMenu() {options = Arrays.asList("continue", "restart", "instructions", "scoreboard","theme", "sound options", "exit");}

    public boolean isSelectedContinue() {return isSelected(0);};
    public boolean isSelectedRestart() {return isSelected(1);};
    public boolean isSelectedInstructions() {return isSelected(2);};
    public boolean isSelectedScoreboard() {return isSelected(3);};
    public boolean isSelectedTheme() {return isSelected(4);};
    public boolean isSelectedSoundOptions(){ return isSelected(5);}
    public boolean isSelectedExit() {return isSelected(6);};
}
