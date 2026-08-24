package Space_Invaders.Viewer.Game;

import Space_Invaders.GUI.GUI;
import Space_Invaders.Model.Game.GameElements.Enemy;
import Space_Invaders.Model.Game.GameElements.EnemyState;
import Space_Invaders.State.Theme;
import com.googlecode.lanterna.graphics.ThemeDefinition;

public class EnemyViewer implements ElementViewer<Enemy> {
    final private  char[] EnemyChars = {Theme.getTheme().enemy1,Theme.getTheme().enemy2,Theme.getTheme().enemy3};

    final private char[] EnemyChars2 = {Theme.getTheme().enemy_1,Theme.getTheme().enemy_2, Theme.getTheme().enemy_3};

    final private String [] EnemyColors = {Theme.getTheme().enemy1Color, Theme.getTheme().enemy2Color, Theme.getTheme().enemy3Color};

    private final int charChoice;

    public EnemyViewer(int charChoice){
        this.charChoice = charChoice;
    }

    @Override
    public void draw(GUI gui, Enemy enemy){
        int EnemyType = enemy.getType();
        if(enemy.getEnemyState() == EnemyState.PURGATORY){
            enemy.setEnemyState(EnemyState.DEAD);
            gui.drawElement(enemy.getPosition(), Theme.getTheme().enemyDeathChar, EnemyColors[EnemyType]);
        }
        else if(charChoice == 0) {
            gui.drawElement(enemy.getPosition(), EnemyChars[EnemyType], EnemyColors[EnemyType]);
        }
        else if(charChoice == 1){
            gui.drawElement(enemy.getPosition(),EnemyChars2[EnemyType], EnemyColors[EnemyType]);

        }
    }


}
