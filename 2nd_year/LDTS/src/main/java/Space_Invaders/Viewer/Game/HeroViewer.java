package Space_Invaders.Viewer.Game;

import Space_Invaders.GUI.GUI;
import Space_Invaders.Model.Game.GameElements.Hero;
import Space_Invaders.State.Theme;

public class HeroViewer implements ElementViewer<Hero> {

    final private char HeroChar = Theme.getTheme().hero;
    final private char HeroDeathChar = Theme.getTheme().deathChar;
    final private String HeroColor = Theme.getTheme().heroColor;

    @Override
    public void draw(GUI gui, Hero element) {

        if (element.getHealth() <= 0)
            gui.drawElement(element.getPosition(), HeroDeathChar, HeroColor);

        gui.drawElement(element.getPosition(), HeroChar, HeroColor );
    }

}
