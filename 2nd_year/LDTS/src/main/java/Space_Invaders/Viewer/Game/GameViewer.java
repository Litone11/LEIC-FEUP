package Space_Invaders.Viewer.Game;

import Space_Invaders.GUI.GUI;
import Space_Invaders.Model.Game.Arena;
import Space_Invaders.Model.Game.Element;
import Space_Invaders.Model.Position;
import Space_Invaders.State.Theme;
import Space_Invaders.Viewer.Viewer;

import java.util.List;

public class GameViewer extends Viewer<Arena> {
    private int enemyCharChoice = 0;
    private long lastCharChange = 0;

    public GameViewer(Arena arena) {super(arena);}

    @Override
    protected void drawElements(GUI gui, long time){
        drawElements(gui, getModel().getEnemies(), new EnemyViewer(enemyCharChoice));
        ChangeChar(time);
        drawElements(gui,getModel().getCoverWalls(), new CoverWallViewer());
        drawElements(gui, getModel().getWalls(), new WallViewer());
        drawElement(gui, getModel().getHero(), new HeroViewer());
        drawElements(gui, getModel().getBullets(), new BulletViewer());
        drawElement(gui,getModel().getFlyEnemy(),new FlyEnemyViewer());
        drawGameHUD(gui);

    }

    private void ChangeChar(long time){
        if(time - lastCharChange > 300) {
            enemyCharChoice++;
            enemyCharChoice = enemyCharChoice % 2;
            lastCharChange = time;
        }
    }

    public int getEnemyCharChoice(){
        return enemyCharChoice;
    }

    public long getLastCharChange(){
        return lastCharChange;
    }

    private void drawGameHUD(GUI gui){
        gui.drawText(new Position(13,3), "score ", Theme.getTheme().hudScore);
        gui.drawText(new Position(14,4), String.valueOf(getModel().getScore()),Theme.getTheme().hudScoreColor );
        gui.drawText(new Position(28,3), "health ", Theme.getTheme().hudHealth);
        gui.drawText(new Position(29,4), String.valueOf(getModel().getHero().getHealth()),Theme.getTheme().hudHealthColor );
        gui.drawText(new Position(43,3),"round ",Theme.getTheme().hudRound);
        gui.drawText(new Position(45,4),String.valueOf(getModel().getRound()),Theme.getTheme().hudRoundColor );

    }

    private <T extends Element> void drawElements(GUI gui, List<T> elements, ElementViewer<T> viewer) {
        for (T element : elements)
            drawElement(gui, element, viewer);
    }

    private <T extends Element> void drawElement(GUI gui, T element, ElementViewer<T> viewer) {
        if(element != null) {
            viewer.draw(gui, element);
        }
    }
}
