package Space_Invaders.Model.Game;


import Space_Invaders.Model.Game.GameElements.*;
import Space_Invaders.Model.Position;
import Space_Invaders.State.Theme;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;
import java.util.Random;

public class ArenaModifier {

    private Arena arena;

    private Random random;

    public ArenaModifier(Arena arena){
        this.arena = arena;
        this.random = new Random();
    }

    public Arena getArena() {return arena;}

    public void setRandom(Random random){this.random = random;}





    public void directionFlyEnemy() {
        List<Integer> moveOptions = new ArrayList<>(Arrays.asList(-1,1));
        List<Integer> scoreOptions = new ArrayList<>(Arrays.asList(50, 100, 150, 200, 250));
        int moveIndex = random.nextInt(moveOptions.size());
        int scoreIndex = random.nextInt(scoreOptions.size());
        int movement = moveOptions.get(moveIndex);
        int score = scoreOptions.get(scoreIndex);
        int x ;
        if(movement == -1){
            x = arena.getWidth() - 2;
        }
        else {
            x = 1;
        }
        FlyEnemy flyEnemy = new FlyEnemy(new Position(x, 8),10 , FlyEnemyState.ALIVE, score, movement);
        arena.setFlyEnemy(flyEnemy);
    }

    public boolean hasEnemyInFront(Enemy enemy, Enemy excludedEnemy){
        List<Enemy> enemies = getArena().getEnemies();
        int i = 0;
        while (i < enemies.size() && enemies.get(i).getPosition().getX() <= enemy.getPosition().getX()){
            if(enemies.get(i).getPosition().getX() == enemy.getPosition().getX()){
                if(enemies.get(i).getPosition().getY() > enemy.getPosition().getY() && !enemies.get(i).equals(excludedEnemy)){
                    return true;
                }
            }
            i++;
        }
        return false;
    }

    public void removeEnemy(Enemy enemy) {
        List<Enemy> enemies = getArena().getEnemies();
        for(int i = 0; i < enemies.size(); i++){
            if(enemies.get(i).equals(enemy)){
                if(i > 0){
                    if(!hasEnemyInFront(enemies.get(i - 1),enemies.get(i))){
                        enemies.get(i - 1).setEnemyState(EnemyState.ATTACKING);
                    }
                }
                if(enemy.getEnemyState() == EnemyState.DEAD){
                    enemies.remove(i);
                }
                else{
                    enemy.setEnemyState(EnemyState.PURGATORY);
                }
                break;
            }
        }
    }

    public void removeCoverWall(CoverWall coverWall){getArena().getCoverWalls().remove(coverWall);}


    public void addBullet(Bullet bullet) {getArena().getBullets().add(bullet);}

    public void removeBullet(Bullet bullet) {getArena().getBullets().remove(bullet);}

    public void removeFlyEnemy() {
        if(!getArena().getFlyEnemy().isKilled() || getArena().getFlyEnemy().getFlyEnemyState() == FlyEnemyState.DEAD)
            getArena().setFlyEnemy(null);
    }

}


