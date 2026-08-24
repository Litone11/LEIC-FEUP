package Space_Invaders.Viewer.Menu;

import Space_Invaders.GUI.GUI;
import Space_Invaders.Model.Menu.SoundOptionsMenu;
import Space_Invaders.Model.Position;
import Space_Invaders.State.Theme;

public class SoundOptionsMenuViewer extends MenuViewer<SoundOptionsMenu> {
    public SoundOptionsMenuViewer(SoundOptionsMenu soundOptionsMenu) {
        super(soundOptionsMenu, new Position(20, 14));
    }


    @Override
    protected void drawElements(GUI gui, long time) {
        drawOptions(gui);
        gui.drawText(new Position(3,5), Theme.getTheme().hero+"", Theme.getTheme().heroColor);
        gui.drawText(new Position(54,14), Theme.getTheme().hero+"", Theme.getTheme().heroColor);
        gui.drawText(new Position(45,12), Theme.getTheme().enemy3+"", Theme.getTheme().enemy3Color);
        gui.drawText(new Position(40,20), Theme.getTheme().hero+"", Theme.getTheme().heroColor);


        gui.drawText(new Position(14,8), Theme.getTheme().enemy2+"", Theme.getTheme().enemy2Color);
        gui.drawText(new Position(12,30), Theme.getTheme().enemy3+"", Theme.getTheme().enemy3Color);
        gui.drawText(new Position(38,40), Theme.getTheme().enemy2+"", Theme.getTheme().enemy2Color);
        gui.drawText(new Position(48,25), Theme.getTheme().enemy2+"", Theme.getTheme().enemy2Color);

        gui.drawText(new Position(30,34), Theme.getTheme().enemy1+"", Theme.getTheme().enemy1Color);
        gui.drawText(new Position(45,4), Theme.getTheme().enemy1+"", Theme.getTheme().enemy1Color);

        gui.drawText(new Position(10,40), Theme.getTheme().enemy2+"", Theme.getTheme().enemy2Color);
        gui.drawText(new Position(5,22), Theme.getTheme().enemy3+"", Theme.getTheme().enemy3Color);
        gui.drawText(new Position(10,1), Theme.getTheme().hero+"", Theme.getTheme().heroColor);



        super.drawMenuTitle(gui, "SOUND OPTIONS", Theme.getTheme().menuColorTitle2, new Position(24, 5));
    }
}
