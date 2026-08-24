package Space_Invaders.Model.Game;

import Space_Invaders.Model.Game.GameElements.*;
import Space_Invaders.Model.Position;

import java.io.BufferedReader;
import java.io.File;
import java.io.IOException;
import java.io.Reader;
import java.nio.charset.Charset;
import java.nio.file.Files;
import java.nio.file.Path;
import java.util.ArrayList;
import java.util.List;

public class ArenaBuilderByRound extends ArenaConstructor {

    private int round;

    private List<String> arenaLines;

    public ArenaBuilderByRound(int round) throws IOException {
        this.round = round;
        if(round <= 5){
            Path resource = new File(ArenaBuilderByRound.class.getResource("/rounds/round" + round + ".txt").getFile()).toPath();
            Reader fileReader = Files.newBufferedReader(resource, Charset.defaultCharset());
            BufferedReader br = new BufferedReader(fileReader);
            arenaLines = readArenaLines(br);
        }
        else{
            Path resource = new File(ArenaBuilderByRound.class.getResource("/rounds/round3.txt").getFile()).toPath();
            Reader fileReader = Files.newBufferedReader(resource, Charset.defaultCharset());
            BufferedReader br = new BufferedReader(fileReader);
            arenaLines = readArenaLines(br);
        }
    }

    public List<String> readArenaLines(BufferedReader br) throws IOException {
        List<String> arenaLines = new ArrayList<>();
        for (String line; (line = br.readLine()) != null; )
            arenaLines.add(line);
        return arenaLines;
    }

    @Override
    public int getWidth() {
        return arenaLines.get(0).length();
    }

    @Override
    public int getHeight() {
        return arenaLines.size();
    }

    @Override
    public int getRound(){
        return round;
    }

    public void setArenaLines(List<String> arenaLines) {
        this.arenaLines = arenaLines;
    }

    public List<String> getArenaLines(){
        return arenaLines;
    }

    private int getEnemiesAttackLine(){
        int i = 0;
        for(i = arenaLines.size() - 1; i >= 0; i--){
            if(arenaLines.get(i).indexOf('E') != -1){
                break;
            }
        }
        return i;
    }

    @Override
    public Hero createHero() {
        for(int x = 0; x < arenaLines.get(0).length(); x++){
            for(int y = 0; y < arenaLines.size(); y++){
                if(arenaLines.get(y).charAt(x) == 'H'){
                    return new Hero(new Position(x,y),getBaseHeroHealth(), getBaseHeroDamage());
                }
            }
        }
        return null;
    }

    @Override
    public List<Enemy> createEnemies() {
        List<Enemy> enemies = new ArrayList<>();
        for(int x = 0; x < arenaLines.get(0).length(); x++){
            int type = 0;
            for (int y = 0; y < arenaLines.size(); y++){
                if(arenaLines.get(y).charAt(x) == 'E'){
                    if(y == getEnemiesAttackLine()){
                        enemies.add(new Enemy(new Position(x,y),getBaseEnemyHealth() * (int) Math.pow(2,round - 1),getBaseEnemyDamage() * (int) Math.pow(2, round - 1),getBaseEnemyScore() * round, EnemyState.ATTACKING, type));
                        type++;
                    }
                    else{
                        enemies.add(new Enemy(new Position(x,y),getBaseEnemyHealth() * (int) Math.pow(2,round - 1),getBaseEnemyDamage() * (int) Math.pow(2, round - 1),getBaseEnemyScore() * round,EnemyState.PASSIVE,type));
                        type++;
                    }
                }

                if(type >= 3 ){
                    type = 0;
                }
            }
        }
        return enemies;
    }

    @Override
    public List<Wall> createWalls() {
        List<Wall> walls = new ArrayList<>();
        for(int x = 0; x < arenaLines.get(0).length(); x++){
            for(int y = 0; y < arenaLines.size(); y++){
                if(arenaLines.get(y).charAt(x) == '#'){
                    walls.add(new Wall(new Position(x,y)));
                }
            }
        }
        return walls;
    }

    @Override
    public List<CoverWall> createCoverWalls() {
        List<CoverWall> coverWalls = new ArrayList<>();
        for(int x = 0; x < arenaLines.get(0).length(); x++){
            for(int y = 0; y < arenaLines.size(); y++){
                if(arenaLines.get(y).charAt(x) == 'W'){
                    coverWalls.add(new CoverWall(new Position(x,y),getBaseCoverWallHealth()));
                }
            }
        }
        return coverWalls;
    }

}


