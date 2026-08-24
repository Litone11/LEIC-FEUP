package Space_Invaders.Viewer.Game;

import Space_Invaders.GUI.GUI;
import Space_Invaders.Model.Game.GameElements.Bullet;
import Space_Invaders.State.Theme;


public class BulletViewer implements ElementViewer<Bullet> {

    @Override
    public void draw(GUI gui, Bullet element) {
        char BulletChar = Theme.getTheme().bullet;
        String BulletColor = element.getBulletColor();
        gui.drawElement(element.getPosition(), BulletChar,BulletColor);
    }
}
