package Space_Invaders.Model.Game.GameElements;

import Space_Invaders.Model.Position;

public class Enemy extends AttackingSurvivalElement {

    private int score;

    private int Type;

    private EnemyState enemyState;

    public Enemy(Position position, int health, int damagePerShot, int score, EnemyState enemyState, int Type) {
        super(position,health,damagePerShot);
        this.score = score;
        this.enemyState = enemyState;
        if(Type >= 3){
            this.Type = 0;
        }
        else {
            this.Type = Type;
        }
    }




    public int getScore() {
                return score;
    }

    public void setScore(int score) {this.score = score;}

    public EnemyState getEnemyState() {return enemyState;}

    public void setEnemyState(EnemyState enemyState) {this.enemyState = enemyState;}

    public int getType(){
        return Type;
    }
}
