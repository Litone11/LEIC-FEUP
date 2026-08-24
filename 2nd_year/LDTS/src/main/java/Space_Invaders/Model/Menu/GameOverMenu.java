package Space_Invaders.Model.Menu;

import java.util.Arrays;

public class GameOverMenu extends Menu{
    private final Integer score;

    public GameOverMenu(int score){
        this.score = score;
        this.options = Arrays.asList("try again","instructions", "scoreboard", "theme", "sound options" ,"exit");
    }

    public Integer getScore(){
        return score;
    }


    public boolean isSelectedTryAgain() {return isSelected(0);};
    public boolean isSelectedInstructions() {return isSelected(1);};
    public boolean isSelectedScoreboard() {return isSelected(2);};

    public boolean isSelectedSoundOptions(){ return isSelected(4);}
    public boolean isSelectedTheme() {return isSelected(3);};
    public boolean isSelectedExit() {return isSelected(5);};
}
