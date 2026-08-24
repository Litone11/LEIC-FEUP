package Space_Invaders.Controller.Game;

import Space_Invaders.Controller.Sound.SoundManager;
import Space_Invaders.Game;
import Space_Invaders.Model.Game.Arena;
import Space_Invaders.Model.Game.GameElements.Bullet;
import Space_Invaders.Model.Game.GameElements.Hero;
import Space_Invaders.Model.Position;
import Space_Invaders.Model.Sound.Sound_Options;
import com.googlecode.lanterna.input.KeyStroke;
import com.googlecode.lanterna.input.KeyType;

import java.io.IOException;

public class HeroController extends GameController {

    private long moveTime;
    private long shootingTime;

    public HeroController(Arena arena) {
        super(arena);
        this.moveTime = 0;
        this.shootingTime = 0;
    }

    public long getMoveTime() {
        return moveTime;
    }

    public void setMoveTime(long moveTime) {
        this.moveTime = moveTime;
    }

    public long getShootingTime() {
        return shootingTime;
    }

    public void setShootingTime(long shootingTime) {
        this.shootingTime = shootingTime;
    }

    public boolean canMoveHero(Position position){
        return position.getX() > 1 && position.getX() < getModel().getWidth() - 2 ;
    }

    public void moveLeft(){
        Hero hero = getModel().getHero();
        Position heroPosition = hero.getPosition();
        if(canMoveHero(new Position(hero.getPosition().getX() - 1, heroPosition.getY()))){
            hero.setPosition(new Position(heroPosition.getX() - 1, heroPosition.getY()));
        }
    }

    public void moveRight(){
        Hero hero = getModel().getHero();
        Position heroPosition = hero.getPosition();
        if(canMoveHero(new Position(heroPosition.getX() + 1, heroPosition.getY()))){
            hero.setPosition(new Position(heroPosition.getX() + 1, heroPosition.getY()));
        }
    }

    public void shootBullet(){
        Hero hero = getModel().getHero();
        Position bulletPosition = new Position(hero.getPosition().getX(),hero.getPosition().getY());
        Bullet bullet = new Bullet(bulletPosition, hero);
        getArenaModifier().addBullet(bullet);
        SoundManager.getInstance().playSound(Sound_Options.LASER);
    }

    public void hitByBullet(Bullet bullet){
        getModel().getHero().decreaseHealth(bullet.getElement().getDamagePerShot());
    }

    @Override
    public void step(Game game, KeyStroke key, long time) throws IOException {
        if(key == null){
            return;
        }
        if(key.getKeyType() == KeyType.ArrowLeft && time - moveTime > 50){
            moveLeft();
            moveTime = time;
        }
        if(key.getKeyType() == KeyType.ArrowRight && time - moveTime > 50){
            moveRight();
            moveTime = time;
        }

        if(key.getKeyType() == KeyType.Character && key.getCharacter() == ' ' && time - shootingTime > 300){
            shootBullet();
            shootingTime = time;
        }

    }
    
}
