package Space_Invaders.Model.Menu;

import java.util.Arrays;

public class StartMenu extends Menu{
    public StartMenu() {options = Arrays.asList("start", "instructions", "scoreboard", "theme", "sound options", "exit" );}

    public boolean isSelectedPlay(){ return isSelected(0);}
    public boolean isSelectedInstructions(){ return isSelected(1);}
    public boolean isSelectedScoreboard(){ return isSelected(2);}
    public boolean isSelectedTheme(){ return isSelected(3);}
    public boolean isSelectedSoundOptions(){ return isSelected(4);}
    public boolean isSelectedExit(){ return isSelected(5);}
}

