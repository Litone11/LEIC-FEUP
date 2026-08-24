package Space_Invaders.State;

import Space_Invaders.Controller.Sound.SoundManager;
import Space_Invaders.Model.Sound.Sound;
import Space_Invaders.Utils.Colors;

import Space_Invaders.Utils.Sounds;

import java.io.IOException;

public class Theme {
    private ThemeState currentThemeState;



    public String title;

    public String menuColorSelected;
    public String menuColor;
    public String menuColorTitle1;
    public String menuColorTitle2;

    public char bullet;
    public String bulletColor;

    public char coverWall1;
    public char coverWall2;
    public char coverWall3;
    public String coverWallColor;

    public char wall;
    public String wallColor;

    public char enemy1;
    public char enemy2;
    public char enemy3;
    public char enemy_1;
    public char enemy_2;
    public char enemy_3;
    public String enemy1Color;
    public String enemy2Color;
    public String enemy3Color;

    public char flyEnemy;
    public String flyEnemyColor;

    public char hero;
    public String heroColor;

    public char deathChar;
    public char enemyDeathChar;

    public String hudScore;
    public String hudHealth;
    public String hudRound;
    public String hudScoreColor;
    public String hudHealthColor;
    public String hudRoundColor;

    public Sound laser;
    public Sound dyingSound;
    public Sound switchOption;
    public Sound backgroundMusic;
    public Sound flyEnemyLowPitch;
    public Sound flyEnemyHighPitch;
    public Sound gameOver;
    public Sound enter;
    
    private static Theme instance;

    private Theme() {
        currentThemeState = ThemeState.SPACE;
        ThemeActions();
    }

    public static Theme getTheme() {
        if (instance == null) {
            instance = new Theme();
        }
        return instance;
    }

    public void updateTheme(ThemeState newTheme) throws IOException {
        currentThemeState = newTheme;
        ThemeActions();
        SoundManager.getInstance().updateSounds();
    }

    public void ThemeActions() {
        switch (currentThemeState) {
            case SPACE:
                title = "SPÒCE";

                menuColor = Colors.WHITE;
                menuColorSelected = Colors.PURPLE;
                menuColorTitle1 = Colors.YELLOW;
                menuColorTitle2 = Colors.PURPLE;

                bullet = '|';

                coverWall1 = '(';
                coverWall2 = ')';
                coverWall3 = '*';
                coverWallColor = Colors.REDPACMAN;

                wall = '(';
                wallColor = Colors.WHITE;

                enemy1 = 'À';
                enemy2 = 'È';
                enemy3 = 'Ì';
                enemy_1 = 'Á';
                enemy_2 = 'É';
                enemy_3 = 'Í';
                enemy1Color = Colors.GREEN;
                enemy2Color = Colors.BLUEPACMAN;
                enemy3Color = Colors.PURPLE;

                flyEnemy = 'Ò';
                flyEnemyColor = Colors.YELLOW;

                hero = 'Ó';
                heroColor = Colors.WALLPACMAN;

                deathChar = '+';
                enemyDeathChar = '=';
                hudScore = Colors.GREEN;
                hudHealth = Colors.GREEN;
                hudRound = Colors.GREEN;
                hudScoreColor = Colors.WHITE;
                hudHealthColor = Colors.WHITE;
                hudRoundColor = Colors.WHITE;

                laser = Sounds.laser1;
                dyingSound = Sounds.dyingSound1;
                switchOption = Sounds.switchOption1;
                backgroundMusic = Sounds.backgroundMusic1;
                flyEnemyHighPitch = Sounds.flyEnemyHighPitch1;
                flyEnemyLowPitch = Sounds.flyEnemyLowPitch1;
                gameOver = Sounds.gameOver1;
                enter = Sounds.enter1;
                break;


            case PACMAN:
                title = "PAØÚÚÚÚ";
                menuColor = Colors.WHITE;
                menuColorSelected = Colors.YELLOWPACMAN;
                menuColorTitle1 = Colors.YELLOWPACMAN;
                menuColorTitle2 = Colors.WALLPACMAN;

                bullet = 'Ú';


                coverWall1 = '(';
                coverWall2 = ')';
                coverWall3 = '*';
                coverWallColor = Colors.GREEN;

                wall = '(';
                wallColor = Colors.WALLPACMAN;

                enemy1 = 'Ù';
                enemy2 = 'Ù';
                enemy3 = 'Ù';
                enemy_1 = 'Ù';
                enemy_2 = 'Ù';
                enemy_3 = 'Ù';
                enemy1Color = Colors.ROSEPACMAN;
                enemy2Color = Colors.BLUEPACMAN;
                enemy3Color = Colors.REDPACMAN;

                flyEnemy = 'Ù';
                flyEnemyColor = Colors.WHITE;

                hero = 'Ø';
                heroColor = Colors.YELLOWPACMAN;

                deathChar = '+';
                enemyDeathChar = '=';

                hudScore = Colors.YELLOWPACMAN;
                hudHealth = Colors.YELLOWPACMAN;
                hudRound = Colors.YELLOWPACMAN;
                hudScoreColor = Colors.WHITE;
                hudHealthColor = Colors.WHITE;
                hudRoundColor = Colors.WHITE;

                laser = Sounds.laser2;
                dyingSound = Sounds.dyingSound2;
                switchOption = Sounds.switchOption2;
                backgroundMusic = Sounds.backgroundMusic2;
                flyEnemyHighPitch = Sounds.flyEnemyHighPitch2;
                flyEnemyLowPitch = Sounds.flyEnemyLowPitch2;
                gameOver = Sounds.gameOver2;
                enter = Sounds.enter2;

                break;
            default:

        }
    }
}
