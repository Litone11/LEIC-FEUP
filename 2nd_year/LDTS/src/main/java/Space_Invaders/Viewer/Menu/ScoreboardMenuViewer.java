package Space_Invaders.Viewer.Menu;

import Space_Invaders.GUI.GUI;
import Space_Invaders.Model.Menu.ScoreboardMenu;
import Space_Invaders.Model.Position;
import Space_Invaders.State.Theme;
import Space_Invaders.Utils.ScoreManager;

import java.util.Collections;
import java.util.List;

public class ScoreboardMenuViewer extends MenuViewer<ScoreboardMenu> {
    public ScoreboardMenuViewer(ScoreboardMenu menu) {super(menu, new Position(40,40));}

    @Override
    protected void drawElements(GUI gui, long time) {
        drawOptions(gui);
        List<Integer> scores = ScoreManager.getScores();

        gui.drawText(new Position(40,15 ),"recent", Theme.getTheme().menuColorSelected);

        for(int i = 0; i < scores.size(); i++){
            if(i == 3)break;
            gui.drawText(new Position(41,18+(i*4)), String.valueOf(scores.get(scores.size()-i-1)), Theme.getTheme().menuColor);

        }

        if(!scores.isEmpty()) {
            gui.drawText(new Position(15,23),String.valueOf(ScoreManager.getHighestScore()), Theme.getTheme().menuColor);
        }

        gui.drawText(new Position(12,19),"high score", Theme.getTheme().menuColorSelected);




        gui.drawText(new Position(3,5), Theme.getTheme().hero+"", Theme.getTheme().heroColor);
        gui.drawText(new Position(54,14), Theme.getTheme().hero+"", Theme.getTheme().heroColor);
        gui.drawText(new Position(33,12), Theme.getTheme().hero+"", Theme.getTheme().heroColor);


        gui.drawText(new Position(10,8), Theme.getTheme().enemy2+"", Theme.getTheme().enemy2Color);
        gui.drawText(new Position(5,30), Theme.getTheme().enemy2+"", Theme.getTheme().enemy2Color);
        gui.drawText(new Position(26,40), Theme.getTheme().enemy2+"", Theme.getTheme().enemy2Color);
        gui.drawText(new Position(53,38), Theme.getTheme().enemy2+"", Theme.getTheme().enemy2Color);

        gui.drawText(new Position(30,34), Theme.getTheme().enemy1+"", Theme.getTheme().enemy1Color);
        gui.drawText(new Position(7,16), Theme.getTheme().enemy1+"", Theme.getTheme().enemy1Color);
        gui.drawText(new Position(45,4), Theme.getTheme().enemy1+"", Theme.getTheme().enemy1Color);

        gui.drawText(new Position(10,40), Theme.getTheme().enemy3+"", Theme.getTheme().enemy3Color);
        gui.drawText(new Position(29,22), Theme.getTheme().enemy3+"", Theme.getTheme().enemy3Color);
        gui.drawText(new Position(17,1), Theme.getTheme().enemy3+"", Theme.getTheme().enemy3Color);



        super.drawMenuTitle(gui, "BOARD", Theme.getTheme().menuColorTitle2, new Position(29, 5));
        gui.drawText(new Position(25,3), "SCORE", Theme.getTheme().menuColorTitle1);
    }
}
