package Space_Invaders.Model.Game.GameElements;

import Space_Invaders.Model.Game.Element;
import Space_Invaders.Model.Position;
import Space_Invaders.State.Theme;
import Space_Invaders.Utils.Colors;

public class Bullet extends Element {

    private final String bulletColor;

    private final AttackingSurvivalElement attackingSurvivalElement;

    public Bullet(Position position, AttackingSurvivalElement attackingSurvivalElement) {
        super(position);
        this.attackingSurvivalElement = attackingSurvivalElement;
        if(attackingSurvivalElement instanceof Enemy enemy) {
            int enemyType = enemy.getType();
            switch(enemyType) {
                case 0:
                    this.bulletColor = Theme.getTheme().enemy1Color;
                    break;
                case 1:
                    this.bulletColor = Theme.getTheme().enemy2Color;
                    break;
                default:
                    this.bulletColor = Theme.getTheme().enemy3Color;
                    break;

            }
        }
        else
            this.bulletColor = Theme.getTheme().heroColor;

    }


    public AttackingSurvivalElement getElement(){ return attackingSurvivalElement;}


    @Override
    public Position getPosition() {
        return super.getPosition();
    }

    public String getBulletColor() {
        return bulletColor;
    }
}
