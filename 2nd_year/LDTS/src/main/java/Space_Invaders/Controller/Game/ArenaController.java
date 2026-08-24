package Space_Invaders.Controller.Game;

import Space_Invaders.Game;
import Space_Invaders.Model.Game.Arena;
import Space_Invaders.Model.Game.ArenaModifier;
import Space_Invaders.Model.Game.Element;
import Space_Invaders.Model.Game.GameElements.*;
import Space_Invaders.State.GameState;
import Space_Invaders.Utils.ScoreManager;
import com.googlecode.lanterna.input.KeyStroke;
import com.googlecode.lanterna.input.KeyType;

import java.io.IOException;
import java.util.List;

public class ArenaController extends GameController {

    private ArenaModifier arenaModifier;

    private HeroController heroController;

    private EnemyController enemyController;

    private FlyEnemyController flyEnemyController;

    private BulletController bulletController;

    private long pauseTime;

    private boolean timeIsWrong;
    
    public ArenaController(Arena arena) {
        super(arena);
        this.arenaModifier = new ArenaModifier(arena);
        this.heroController = new HeroController(arena);
        this.enemyController = new EnemyController(arena);
        this.flyEnemyController = new FlyEnemyController(arena);
        this.bulletController = new BulletController(arena);
        this.timeIsWrong = false;
        this.pauseTime = 0;
    }

    @Override
    public ArenaModifier getArenaModifier() {
        return arenaModifier;
    }

    public void setArenaModifier(ArenaModifier arenaModifier) {
        this.arenaModifier = arenaModifier;
    }

    public HeroController getHeroController() {
        return heroController;
    }

    public void setHeroController(HeroController heroController) {
        this.heroController = heroController;
    }

    public EnemyController getEnemyController() {
        return enemyController;
    }

    public void setEnemyController(EnemyController enemyController) {
        this.enemyController = enemyController;
    }

    public FlyEnemyController getFlyEnemyController() {
        return flyEnemyController;
    }

    public void setFlyEnemyController(FlyEnemyController flyEnemyController) {
        this.flyEnemyController = flyEnemyController;
    }

    public BulletController getBulletController() {
        return bulletController;
    }

    public void setBulletController(BulletController bulletController) {
        this.bulletController = bulletController;
    }

    public long getPauseTime() {
        return pauseTime;
    }

    public void setPauseTime(long pauseTime) {
        this.pauseTime = pauseTime;
    }

    public boolean isUpdateTime() {
        return timeIsWrong;
    }

    public void setTimeIsWrong(boolean updateTime) {
        this.timeIsWrong = updateTime;
    }


    public boolean timersWrong() {
        return timeIsWrong;
    }

    public void setTimers(long time){
        long timeGameWasPaused = time - pauseTime;
        heroController.setMoveTime(heroController.getMoveTime() + timeGameWasPaused);
        heroController.setShootingTime(heroController.getShootingTime() + timeGameWasPaused);
        enemyController.setLastMoveTime(enemyController.getLastMoveTime() + timeGameWasPaused);
        enemyController.setLastShotTime(enemyController.getLastShotTime() + timeGameWasPaused);
        flyEnemyController.setLastMoveTime(flyEnemyController.getLastMoveTime() + timeGameWasPaused);
        flyEnemyController.setLastApp(flyEnemyController.getLastApp() + timeGameWasPaused);
    }

    public boolean impact(Element element1, Element element2){
        return element1.getPosition().equals(element2.getPosition());
    }

    public boolean heroImpactEnemy(){
        Hero hero = getModel().getHero();
        List<Enemy> enemies = getModel().getEnemies();
        for(Enemy enemy : enemies){
            if(impact(hero,enemy)){
                return true;
            }
        }
        return false;
    }

    public boolean enemyImpactCoverWall(){
        List<CoverWall> coverWalls = getModel().getCoverWalls();
        List<Enemy> enemies = getModel().getEnemies();
        for(Enemy enemy : enemies){
            for(CoverWall coverWall : coverWalls){
                if(impact(enemy,coverWall)){
                    return true;
                }
            }
        }
        return false;
    }

    public boolean enemyReachesBottomArenaWall(){
        List<Enemy> enemies = getModel().getEnemies();
        for(Enemy enemy : enemies){
            if(enemy.getPosition().getY() >= getModel().getHeight() - 1){
                return true;
            }
        }
        return false;
    }

    public void bulletImpactWalls(){
        List<Bullet> bullets = getModel().getBullets();
        List<Wall> walls = getModel().getWalls();
        for(int i = 0; i < walls.size(); i++){
            for(int j = 0; j < bullets.size(); j++){
                if(impact(walls.get(i),bullets.get(j))){
                    this.getArenaModifier().removeBullet(bullets.get(j));
                }
            }
        }
    }

    public void bulletImpactHero(){
        List<Bullet> bullets = getModel().getBullets();
        Hero hero = getModel().getHero();
        for(int i = 0; i < bullets.size(); i++){
            if(impact(hero,bullets.get(i))){
                this.getHeroController().hitByBullet(bullets.get(i));
                this.getArenaModifier().removeBullet(bullets.get(i));
            }
        }
    }


    public void bulletImpactEnemies(){
        List<Bullet> bullets = getModel().getBullets();
        List<Enemy> enemies = getModel().getEnemies();
        for(int i = 0; i < enemies.size(); i++){
            for (int j = 0; j < bullets.size(); j++){
                if(impact(enemies.get(i), bullets.get(j))){
                    this.getEnemyController().hitByBullet(enemies.get(i),bullets.get(j));
                    this.getArenaModifier().removeBullet(bullets.get(j));
                }
            }
        }
    }

    public void bulletImpactCoverWalls(){
        List<Bullet> bullets = getModel().getBullets();
        List<CoverWall> coverWalls = getModel().getCoverWalls();
        for(int i = 0; i < coverWalls.size(); i++){
            for (int j = 0; j < bullets.size(); j++){
                if(impact(coverWalls.get(i),bullets.get(j))){
                    coverWallHitByBullet(coverWalls.get(i),bullets.get(j));
                    this.getArenaModifier().removeBullet(bullets.get(j));
                }
            }
        }
    }

    public void bulletImpactFlyEnemy(){
        List<Bullet> bullets = getModel().getBullets();
        FlyEnemy flyEnemy = getModel().getFlyEnemy();
        if(flyEnemy != null) {
            for (int i = 0; i < bullets.size(); i++) {
                if (impact(bullets.get(i), flyEnemy) && flyEnemy.getFlyEnemyState() == FlyEnemyState.ALIVE) {
                    this.getFlyEnemyController().hitByBullet(flyEnemy, bullets.get(i));
                    this.getArenaModifier().removeBullet(bullets.get(i));
                }
            }
        }
    }

    

    public void coverWallHitByBullet(CoverWall coverWall, Bullet bullet){
        coverWall.decreaseHealth(bullet.getElement().getDamagePerShot());
    }

    public void removeDestroyedCoverWalls(){
        List<CoverWall> coverWalls = getModel().getCoverWalls();
        for (int i = 0; i < coverWalls.size(); i++){
            if(coverWalls.get(i).isDestroyed()){
                arenaModifier.removeCoverWall(coverWalls.get(i));
            }
        }
    }

    public void removeDestroyedElements(){
        this.getEnemyController().removeDestroyedEnemies();
        removeDestroyedCoverWalls();
        this.getFlyEnemyController().removeFlyEnemy();

    }

    public void checkCollisions(){
        bulletImpactWalls();
        bulletImpactHero();
        bulletImpactEnemies();
        bulletImpactCoverWalls();
        bulletImpactFlyEnemy();
    }

    @Override
    public void step(Game game, KeyStroke key, long time) throws IOException {
        if(timeIsWrong){
            setTimers(time);
            timeIsWrong = false;
        }
        if(key != null){
            if(key.getKeyType() == KeyType.Escape){
                pauseTime = time;
                timeIsWrong = true;
                game.setState(GameState.PAUSE);
            }
        }
        if(getModel().getHero().isDestroyed() || heroImpactEnemy() || enemyImpactCoverWall() || enemyReachesBottomArenaWall()){
            game.setState(GameState.GAME_OVER);
            int score = getModel().getScore();
            ScoreManager.addScore(score);
        }
        if(getModel().getEnemies().isEmpty()){
            game.setState(GameState.NEW_GAME_ROUND);
        }

        heroController.step(game,key,time);
        enemyController.step(game,key,time);
        flyEnemyController.step(game, key, time);
        bulletController.step(game,key,time);
        checkCollisions();
        removeDestroyedElements();
    }
}
