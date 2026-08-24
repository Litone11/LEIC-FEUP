package Space_Invaders.Model.Game.GameElements;

import Space_Invaders.Model.Position;

public class FlyEnemy extends SurvivalElement {

    private int score;
    private int moveDirection;
    private boolean killed;
    private int counter;

    private FlyEnemyState flyEnemyState;

    public FlyEnemy(Position position, int health, FlyEnemyState flyEnemyState, int score, int moveDirection) {
        super(position,health);
        this.score = score;
        this.flyEnemyState = flyEnemyState;
        this.moveDirection = moveDirection;
        this.killed = false;
        this.counter = 0;
    }

    public int getScore() {
        return score;
    }

    public int getMoveDirection() {
        return moveDirection;
    }

    public FlyEnemyState getFlyEnemyState() {
        return flyEnemyState;
    }

    public void setFlyEnemyState(FlyEnemyState flyEnemyState) {
        this.flyEnemyState = flyEnemyState;
    }

    public boolean isKilled() {
        return killed;
    }

    public void setKilled(boolean killed) {
        this.killed = killed;
    }

    public int getCounter() {
        return counter;
    }

    public void increaseCounter() {
        this.counter++;
    }
}
