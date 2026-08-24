package Space_Invaders.Viewer.Game;

import Space_Invaders.GUI.GUI;
import Space_Invaders.Model.Game.GameElements.FlyEnemy;
import Space_Invaders.Model.Game.GameElements.FlyEnemyState;
import Space_Invaders.State.Theme;

public class FlyEnemyViewer implements ElementViewer<FlyEnemy> {
    @Override
    public void draw(GUI gui, FlyEnemy flyEnemy) {
        if (flyEnemy.getFlyEnemyState() == FlyEnemyState.PURGATORY) {
            flyEnemy.setFlyEnemyState(FlyEnemyState.SCORE);
            gui.drawElement(flyEnemy.getPosition(), Theme.getTheme().enemyDeathChar, Theme.getTheme().flyEnemyColor);
        }
        else if (flyEnemy.getFlyEnemyState() == FlyEnemyState.SCORE) {
            flyEnemy.increaseCounter();
            if(flyEnemy.getCounter() == 25)flyEnemy.setFlyEnemyState(FlyEnemyState.DEAD);
            String scoreString = Theme.getTheme().enemyDeathChar + String.valueOf(flyEnemy.getScore());
            gui.drawText(flyEnemy.getPosition(), scoreString, Theme.getTheme().flyEnemyColor);
        }
        else
            gui.drawElement(flyEnemy.getPosition(), Theme.getTheme().flyEnemy, Theme.getTheme().flyEnemyColor);
    }

}
