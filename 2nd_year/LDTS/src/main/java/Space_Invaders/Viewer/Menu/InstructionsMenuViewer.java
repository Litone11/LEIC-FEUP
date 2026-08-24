package Space_Invaders.Viewer.Menu;

import Space_Invaders.GUI.GUI;
import Space_Invaders.Model.Menu.InstructionsMenu;
import Space_Invaders.Model.Position;
import Space_Invaders.State.Theme;

public class InstructionsMenuViewer extends MenuViewer<InstructionsMenu> {
    public InstructionsMenuViewer(InstructionsMenu menu) {super(menu, new Position(40,40));}

    @Override
    protected void drawElements(GUI gui, long time) {
        drawOptions(gui);
        gui.drawText(new Position(5,10), "welcome to  THE INVADERS ! here is how to play!", Theme.getTheme().menuColor);
        gui.drawText(new Position(8,14), " move LEFT >  press the left arrow key", Theme.getTheme().menuColor);
        gui.drawText(new Position(8,16), "move RIGHT >  press the right arrow key", Theme.getTheme().menuColor);
        gui.drawText(new Position(8,18), "   SHOOT   >  press the spacebar to fire", Theme.getTheme().menuColor);

        gui.drawText(new Position(13,22), "your goal is to defeat all invading", Theme.getTheme().menuColor);
        gui.drawText(new Position(13,23), "  aliens and protect your base!", Theme.getTheme().menuColor);

        gui.drawText(new Position(13,29), "dodge enemy fire, aim carefully, and", Theme.getTheme().menuColor);
        gui.drawText(new Position(13,30), "    rack up the highest score!", Theme.getTheme().menuColor);

        gui.drawText(new Position(6,34), "points > ", Theme.getTheme().menuColor);

        gui.drawText(new Position(18,34), Theme.getTheme().enemy1 + "", Theme.getTheme().enemy1Color);
        gui.drawText(new Position(20,34), "10", Theme.getTheme().enemy1Color);

        gui.drawText(new Position(28,34), Theme.getTheme().enemy2 + "", Theme.getTheme().enemy2Color);
        gui.drawText(new Position(30,34), "25", Theme.getTheme().enemy2Color);

        gui.drawText(new Position(38,34), Theme.getTheme().enemy3 + "", Theme.getTheme().enemy3Color);
        gui.drawText(new Position(40,34), "50", Theme.getTheme().enemy3Color);

        gui.drawText(new Position(48,34), Theme.getTheme().flyEnemy + "", Theme.getTheme().flyEnemyColor);
        gui.drawText(new Position(50,34), "???", Theme.getTheme().flyEnemyColor);






        gui.drawText(new Position(7,40),   "good luck commander!", Theme.getTheme().menuColorTitle1);






        gui.drawText(new Position(3,5), Theme.getTheme().hero+"", Theme.getTheme().heroColor);
        gui.drawText(new Position(54,14), Theme.getTheme().hero+"", Theme.getTheme().heroColor);
        gui.drawText(new Position(52,3), Theme.getTheme().hero+"", Theme.getTheme().heroColor);


        gui.drawText(new Position(10,5), Theme.getTheme().enemy2+"", Theme.getTheme().enemy2Color);
        gui.drawText(new Position(48,25), Theme.getTheme().enemy2+"", Theme.getTheme().enemy2Color);

        gui.drawText(new Position(2,13), Theme.getTheme().enemy1+"", Theme.getTheme().enemy1Color);
        gui.drawText(new Position(45,4), Theme.getTheme().enemy1+"", Theme.getTheme().enemy1Color);

        gui.drawText(new Position(8,21), Theme.getTheme().enemy3+"", Theme.getTheme().enemy3Color);
        gui.drawText(new Position(17,1), Theme.getTheme().enemy3+"", Theme.getTheme().enemy3Color);



        super.drawMenuTitle(gui, "INSTRUCTIONS", Theme.getTheme().menuColorTitle2, new Position(24, 5));
    }
}
