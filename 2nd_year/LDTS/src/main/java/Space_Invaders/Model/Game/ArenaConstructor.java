package Space_Invaders.Model.Game;

import Space_Invaders.Model.Game.GameElements.CoverWall;
import Space_Invaders.Model.Game.GameElements.Enemy;
import Space_Invaders.Model.Game.GameElements.Hero;

import Space_Invaders.Model.Game.GameElements.Wall;

import java.util.ArrayList;
import java.util.List;

public abstract class ArenaConstructor {

    private int baseHeroHealth;

    private int baseHeroDamage;

    private int baseEnemyHealth;

    private int baseEnemyDamage;

    private int baseEnemyScore;

    private int baseCoverWallHealth;



    public Arena buildArena(){
        Arena arena = new Arena(getWidth(),getHeight());
        baseHeroHealth = 100;
        baseHeroDamage = 34;
        baseEnemyHealth = 10;
        baseEnemyDamage = 34;
        baseEnemyScore = 20;
        baseCoverWallHealth = 100;
        arena.setRound(getRound());
        arena.setHero(createHero());
        arena.setEnemies(createEnemies());
        arena.setWalls(createWalls());
        arena.setCoverWalls(createCoverWalls());
        arena.setBullets(new ArrayList<>());
        return arena;
    }

    public int getBaseHeroHealth() {return baseHeroHealth;}

    public int getBaseHeroDamage() {
        return baseHeroDamage;
    }

    public int getBaseEnemyHealth() {
        return baseEnemyHealth;
    }

    public int getBaseEnemyDamage() {
        return baseEnemyDamage;
    }

    public int getBaseEnemyScore() {
        return baseEnemyScore;
    }

    public int getBaseCoverWallHealth() {
        return baseCoverWallHealth;
    }

    public abstract int getWidth();

    public abstract int getHeight();

    public abstract int getRound();

    public abstract Hero createHero();

    public abstract List<Enemy> createEnemies();

    public abstract List<Wall> createWalls();

    public abstract List<CoverWall> createCoverWalls();

}


