package Space_Invaders.Controller.Game;

import Space_Invaders.Controller.Sound.SoundManager;
import Space_Invaders.Game;
import Space_Invaders.Model.Game.Arena;
import Space_Invaders.Model.Game.GameElements.Bullet;
import Space_Invaders.Model.Game.GameElements.FlyEnemy;
import Space_Invaders.Model.Game.GameElements.FlyEnemyState;
import Space_Invaders.Model.Position;
import com.googlecode.lanterna.input.KeyStroke;
import Space_Invaders.Model.Sound.Sound_Options;

import java.io.IOException;

public class FlyEnemyController extends GameController {
    private long lastApp;
    private final long appTime = 25000;
    private long lastMoveTime;

    public FlyEnemyController(Arena arena){
        super(arena);
        this.lastApp = 0;
        this.lastMoveTime = 0;
    }


    public long getLastApp() {
        return lastApp;
    }

    public void setLastApp(long lastApp) {
        this.lastApp = lastApp;
    }

    public long getLastMoveTime() {
        return lastMoveTime;
    }

    public void setLastMoveTime(long lastMoveTime) {
        this.lastMoveTime = lastMoveTime;
    }

    public void createFlyEnemy(){
        getArenaModifier().directionFlyEnemy();
        SoundManager.getInstance().playSound(Sound_Options.FLY_ENEMY_HIGH);
        SoundManager.getInstance().playSound(Sound_Options.FLY_ENEMY_LOW);
    }

    public void moveFlyEnemy(){
        FlyEnemy flyEnemy = getModel().getFlyEnemy();
        if(canMoveFlyEnemy() && flyEnemy.getFlyEnemyState() == FlyEnemyState.ALIVE) {
            flyEnemy.setPosition(new Position(flyEnemy.getPosition().getX() + flyEnemy.getMoveDirection(), flyEnemy.getPosition().getY()));
        }
        else{
            getArenaModifier().removeFlyEnemy();

            SoundManager.getInstance().stopSound(Sound_Options.FLY_ENEMY_HIGH);
            SoundManager.getInstance().stopSound(Sound_Options.FLY_ENEMY_LOW);
        }
    }

    public boolean canMoveFlyEnemy(){
        FlyEnemy flyEnemy = getModel().getFlyEnemy();
        int moveDirection = flyEnemy.getMoveDirection();
        if(moveDirection == 1)
            return flyEnemy.getPosition().getX() + 2 < getModel().getWidth();
        else
            return flyEnemy.getPosition().getX() - 1> 0;
    }

    public void removeFlyEnemy(){
        FlyEnemy flyEnemy = getModel().getFlyEnemy();
        if(flyEnemy != null) {
            if (flyEnemy.isDestroyed()) {
                    getArenaModifier().removeFlyEnemy();
                    SoundManager.getInstance().playSound(Sound_Options.DYING_SOUND);
                    SoundManager.getInstance().stopSound(Sound_Options.FLY_ENEMY_LOW);
                    SoundManager.getInstance().stopSound(Sound_Options.FLY_ENEMY_HIGH);
            }
        }
    }


    public void hitByBullet(FlyEnemy flyEnemy, Bullet bullet){
        flyEnemy.decreaseHealth(bullet.getElement().getDamagePerShot());
        if(getModel().getFlyEnemy().isDestroyed()) {
            getModel().increaseScore(flyEnemy.getScore());
            getModel().getFlyEnemy().setKilled(true);
            flyEnemy.setFlyEnemyState(FlyEnemyState.PURGATORY);
        }
    }
    @Override
    public void step(Game game, KeyStroke key, long time) throws IOException {
        if(time - lastApp > appTime){
            createFlyEnemy();
            lastApp= time;
        }
        else if(getModel().getFlyEnemy() != null){
            if(time - lastMoveTime > stepTime) {
                moveFlyEnemy();
                lastMoveTime = time;
            }
        }
    }
}
