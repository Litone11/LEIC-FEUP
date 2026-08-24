package Space_Invaders.Controller.Game;

import Space_Invaders.Controller.Sound.SoundManager;
import Space_Invaders.Game;
import Space_Invaders.Model.Game.Arena;
import Space_Invaders.Model.Game.GameElements.Bullet;
import Space_Invaders.Model.Game.GameElements.Enemy;
import Space_Invaders.Model.Position;
import Space_Invaders.Model.Sound.Sound_Options;
import com.googlecode.lanterna.input.KeyStroke;

import java.util.List;
import java.util.Random;

public class EnemyController extends GameController {

    private MovementDirection movementDirection;
    private long lastMoveTime;
    private long lastShotTime;

    public EnemyController(Arena arena){
        super(arena);
        this.movementDirection = MovementDirection.RIGHT;
        this.lastMoveTime = 0;
        this.lastShotTime = 0;
    }

    public MovementDirection getMovementDirection() {
        return movementDirection;
    }

    public void setMovementDirection(MovementDirection movementDirection) {
        this.movementDirection = movementDirection;
    }

    public long getLastMoveTime() {
        return lastMoveTime;
    }

    public void setLastMoveTime(long lastMoveTime) {
        this.lastMoveTime = lastMoveTime;
    }

    public long getLastShotTime() {
        return lastShotTime;
    }

    public void setLastShotTime(long lastShotTime) {
        this.lastShotTime = lastShotTime;
    }

    public boolean canMoveEnemies(){
        List<Enemy> enemies = getModel().getEnemies();
        for(Enemy enemy : enemies){
            if(!canMoveEnemy(enemy)){
                return false;
            }
        }
        return true;
    }

    public void moveEnemy(Enemy enemy){
        Position enemyPosition = new Position(enemy.getPosition().getX(),enemy.getPosition().getY());
        switch(this.getMovementDirection()){
            case LEFT:
                enemy.setPosition(new Position(enemyPosition.getX() - 1,enemyPosition.getY()));
                break;
            case RIGHT:
                enemy.setPosition(new Position(enemyPosition.getX() + 1,enemyPosition.getY()));
                break;
            case DOWN:
                enemy.setPosition(new Position(enemyPosition.getX(),enemyPosition.getY() + 1));
                break;
        }
    }
    

    public boolean canMoveEnemy(Enemy enemy) {
        return switch (this.getMovementDirection()) {
            case LEFT:
                yield enemy.getPosition().getX() - 3 > 0;
            case RIGHT:
                yield enemy.getPosition().getX() + 3 < getModel().getWidth() - 1;
            case DOWN:
                yield true;
        };
    }

    public void moveEnemies(){
        List<Enemy> enemies = getModel().getEnemies();
        for(Enemy enemy: enemies){
            moveEnemy(enemy);
        }
    }

    public void shootBullet(){
        List<Enemy> attackingEnemies = getModel().getAttackingEnemies();
        if(!getModel().getAttackingEnemies().isEmpty()){
            Random random = new Random();
            int randomIndex = random.nextInt(attackingEnemies.size());
            Enemy randomEnemy = attackingEnemies.get(randomIndex);
            getArenaModifier().addBullet(new Bullet(randomEnemy.getPosition(),randomEnemy));
        }
    }

    public void hitByBullet(Enemy enemy, Bullet bullet){
        enemy.decreaseHealth(bullet.getElement().getDamagePerShot());
        getModel().increaseScore(enemy.getScore());
    }

    public void removeDestroyedEnemies(){
        List<Enemy> enemies = getModel().getEnemies();
        for(int i = 0; i < enemies.size(); i++){
            if(enemies.get(i).isDestroyed()){
                getArenaModifier().removeEnemy(enemies.get(i));
                SoundManager.getInstance().playSound(Sound_Options.DYING_SOUND);
            }
        }

    }

    public void updateMovementDirection(){
        switch (this.getMovementDirection()){
            case LEFT:
                if(!canMoveEnemies()){
                    this.movementDirection = MovementDirection.DOWN;
                }
                break;
            case RIGHT:
                if(!canMoveEnemies()){
                    this.movementDirection = MovementDirection.LEFT;
                }
                break;
            case DOWN:
                this.movementDirection = MovementDirection.RIGHT;
                break;
        }
    }

    public long movementCoolDown(){
        long movementCoolDown = 300 - (getModel().getRound() - 1) * 50L;
        if(movementCoolDown < 100){
            return 50;
        }
        return movementCoolDown;
    }

    public long shootingCoolDown(){
        long shootingCoolDown = 800 - (getModel().getRound() - 1) * 100L;
        if(shootingCoolDown < 200){
            return 100;
        }
        return shootingCoolDown;
    }

    @Override
    public void step(Game game, KeyStroke key, long time) {
        if(time - lastShotTime > shootingCoolDown()){
            shootBullet();
            lastShotTime = time;
        }
        if(time - lastMoveTime > movementCoolDown()){
            updateMovementDirection();
            moveEnemies();
            lastMoveTime = time;
        }
    }
}
