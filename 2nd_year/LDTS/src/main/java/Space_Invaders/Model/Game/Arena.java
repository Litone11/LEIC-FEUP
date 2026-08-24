package Space_Invaders.Model.Game;

import Space_Invaders.Model.Game.GameElements.*;
import Space_Invaders.Model.Position;

import java.util.ArrayList;
import java.util.List;

public class Arena {
    private int width, height, round, score;

    private Hero hero;

    private List<Enemy> enemies;

    private List<Wall> walls;

    private List<CoverWall> coverWalls;

    private List<Bullet> bullets;

    private FlyEnemy flyEnemy;



    public Arena(int width, int height) {
        this.width = width;
        this.height = height;
        this.score = 0;
    }

    public int getWidth() {
        return width;
    }

    public int getHeight(){
        return height;
    }

    public int getRound() {return round;}

    public void setRound(int round) {this.round = round;}

    public int getScore(){
        return score;
    }

    public void increaseScore(int score){this.score+=score;}



    public Hero getHero() {
        return hero;
    }

    public List<Enemy> getEnemies() {
        return enemies;
    }

    public List<Wall> getWalls() {
        return walls;
    }

    public List<CoverWall> getCoverWalls() {
        return coverWalls;
    }

    public List<Bullet> getBullets() {return bullets;}

    public FlyEnemy getFlyEnemy() {return flyEnemy;}





    public void setHero(Hero hero) {
        this.hero = hero;
    }

    public void setEnemies(List<Enemy> enemies) {
        this.enemies = enemies;
    }

    public void setWalls(List<Wall> walls) {
        this.walls = walls;
    }

    public void setCoverWalls(List<CoverWall> coverWalls) {
        this.coverWalls = coverWalls;
    }

    public void setBullets(List<Bullet> bullets) {this.bullets = bullets;}

    public void setFlyEnemy(FlyEnemy flyEnemy) {this.flyEnemy = flyEnemy;}





    public List<Enemy> getAttackingEnemies(){
        List<Enemy> attackingEnemies = new ArrayList<>();
        for(Enemy enemy : enemies){
            if(enemy.getEnemyState() == EnemyState.ATTACKING){
                attackingEnemies.add(enemy);
            }
        }
        return attackingEnemies;
    }


    public boolean freeArenaPosition(Position position){
        for(Enemy enemy : enemies){
            if(enemy.getPosition().equals(position)){
                return false;
            }
        }
        for(CoverWall coverWall : coverWalls){
            if(coverWall.getPosition().equals(position)){
                return false;
            }
        }
        if(flyEnemy != null) {
            return !flyEnemy.getPosition().equals(position);
        }
        return true;
    }


    public List<Integer> getFreeArenaColumns(){
        List<Integer> columns = new ArrayList<>();
        boolean isFree = true;
        for(int x = 0; x < width; x++){
            for(int y = 0; y < height; y++){
                if(!freeArenaPosition(new Position(x,y))){
                    isFree = false;
                    break;
                }
                isFree = true;
            }
            if(isFree){
                columns.add(x);
            }
        }
        return columns;
    }


}

