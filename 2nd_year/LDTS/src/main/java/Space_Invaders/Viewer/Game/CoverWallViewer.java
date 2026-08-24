package Space_Invaders.Viewer.Game;

import Space_Invaders.GUI.GUI;
import Space_Invaders.Model.Game.GameElements.CoverWall;
import Space_Invaders.State.Theme;

public class CoverWallViewer implements ElementViewer<CoverWall> {

    final static char[] coverWallChars= {Theme.getTheme().coverWall1,Theme.getTheme().coverWall2,Theme.getTheme().coverWall3};

    private int selectCharByHealth(int health){
        if(health > 66){
            return 0;
        }

        else if(health > 33){
            return 1;
        }

        else if(health > 0){
            return 2;
        }

        return 0;
    }

    @Override
    public void draw(GUI gui, CoverWall element) {
        String coverWallColor = Theme.getTheme().coverWallColor;
        gui.drawElement(element.getPosition(), coverWallChars[selectCharByHealth(element.getHealth())], coverWallColor);
    }
}
