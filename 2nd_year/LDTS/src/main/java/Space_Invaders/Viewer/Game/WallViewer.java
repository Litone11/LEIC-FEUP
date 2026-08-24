package Space_Invaders.Viewer.Game;

import Space_Invaders.GUI.GUI;
import Space_Invaders.Model.Game.GameElements.Wall;
import Space_Invaders.State.Theme;

public class WallViewer implements ElementViewer<Wall> {

    @Override
    public void draw(GUI gui, Wall element) {
        char wallChar = Theme.getTheme().wall;
        String wallColor = Theme.getTheme().wallColor;
        gui.drawElement(element.getPosition(), wallChar, wallColor);
    }
}
