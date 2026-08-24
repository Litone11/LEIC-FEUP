package Space_Invaders.Viewer.Menu;

import Space_Invaders.GUI.GUI;
import Space_Invaders.Model.Menu.ThemeMenu;
import Space_Invaders.Model.Position;
import Space_Invaders.State.Theme;


public class ThemeMenuViewer extends MenuViewer<ThemeMenu> {
    public ThemeMenuViewer(ThemeMenu menu) {super(menu, new Position(3, 21));}



    @Override
    protected void drawElements(GUI gui, long time) {
        drawOptions(gui);
        gui.drawText(new Position(42,30), "MADE BY", Theme.getTheme().menuColorTitle1);
        gui.drawText(new Position(37,34), "henrique goncalves", Theme.getTheme().menuColor);
        gui.drawText(new Position(40,37), "joao taveira", Theme.getTheme().menuColor);
        gui.drawText(new Position(40,40), "luis martins", Theme.getTheme().menuColor);


        gui.drawText(new Position(3,5), Theme.getTheme().hero+"", Theme.getTheme().heroColor);
        gui.drawText(new Position(54,14), Theme.getTheme().hero+"", Theme.getTheme().heroColor);
        gui.drawText(new Position(33,12), Theme.getTheme().hero+"", Theme.getTheme().heroColor);
        gui.drawText(new Position(40,20), Theme.getTheme().hero+"", Theme.getTheme().heroColor);


        gui.drawText(new Position(10,8), Theme.getTheme().enemy2+"", Theme.getTheme().enemy2Color);
        gui.drawText(new Position(19,30), Theme.getTheme().enemy2+"", Theme.getTheme().enemy2Color);
        gui.drawText(new Position(26,40), Theme.getTheme().enemy2+"", Theme.getTheme().enemy2Color);
        gui.drawText(new Position(48,25), Theme.getTheme().enemy2+"", Theme.getTheme().enemy2Color);

        gui.drawText(new Position(30,34), Theme.getTheme().enemy1+"", Theme.getTheme().enemy1Color);
        gui.drawText(new Position(17,13), Theme.getTheme().enemy1+"", Theme.getTheme().enemy1Color);
        gui.drawText(new Position(45,4), Theme.getTheme().enemy1+"", Theme.getTheme().enemy1Color);

        gui.drawText(new Position(10,40), Theme.getTheme().enemy3+"", Theme.getTheme().enemy3Color);
        gui.drawText(new Position(26,22), Theme.getTheme().enemy3+"", Theme.getTheme().enemy3Color);
        gui.drawText(new Position(17,1), Theme.getTheme().enemy3+"", Theme.getTheme().enemy3Color);





        gui.drawText(new Position(25,3), "choose", Theme.getTheme().menuColorTitle1);
        drawMenuTitle(gui, "THEME", Theme.getTheme().menuColorTitle2, new Position(29,5));    }
}
