package Space_Invaders.Controller.Game;

import Space_Invaders.Game;
import Space_Invaders.Model.Game.Arena;
import Space_Invaders.Model.Game.GameElements.Enemy;
import Space_Invaders.Model.Game.GameElements.Bullet;
import Space_Invaders.Model.Game.GameElements.Hero;
import Space_Invaders.Model.Position;
import com.googlecode.lanterna.input.KeyStroke;

import java.util.List;

public class BulletController extends GameController {


    public BulletController(Arena arena) {
        super(arena);
    }

    public void moveBullets(){
        List<Bullet> bullets = getModel().getBullets();
        for(Bullet bullet : bullets){
            Position bulletPosition = bullet.getPosition();
            if(bullet.getElement() instanceof Hero){
                bullet.setPosition(new Position(bulletPosition.getX(),bulletPosition.getY() - 1));
            }
            if(bullet.getElement() instanceof Enemy){
                bullet.setPosition(new Position(bulletPosition.getX(),bulletPosition.getY() + 1));
            }
        }
    }
    @Override
    public void step(Game game, KeyStroke key, long time) {
        moveBullets();
    }
}
