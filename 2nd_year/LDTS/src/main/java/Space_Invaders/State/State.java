package Space_Invaders.State;

import Space_Invaders.Controller.Controller;
import Space_Invaders.Controller.Game.ArenaController;
import Space_Invaders.Controller.Menu.*;
import Space_Invaders.Controller.Sound.SoundManager;
import Space_Invaders.GUI.GUI;
import Space_Invaders.Game;
import Space_Invaders.Model.Game.Arena;
import Space_Invaders.Model.Game.ArenaBuilderByRound;
import Space_Invaders.Model.Menu.*;
import Space_Invaders.Model.Sound.Sound_Options;
import Space_Invaders.Viewer.Game.GameViewer;

import Space_Invaders.Viewer.Menu.*;
import Space_Invaders.Viewer.Viewer;
import com.googlecode.lanterna.input.KeyStroke;

import java.io.IOException;

public class State {
    private GameState currentGameState;
    private GameState previousGameState;

    private Controller controller;

    private Viewer viewer;

    private Arena arena;

    private ArenaController arenaController;

    private static State instance;

    private State() {
        currentGameState = GameState.START;
        previousGameState = GameState.START;
        StartMenu menu = new StartMenu();
        viewer = new StartMenuViewer(menu);
        controller = new StartMenuController(menu);

    }

    public static State getInstance() {
        if (instance == null) {
            instance = new State();
        }
        return instance;
    }


    public GameState getCurrentGameState() {
        return currentGameState;
    }

    public void setCurrentGameState(GameState currentGameState) {
        this.currentGameState = currentGameState;
    }

    public GameState getPreviousGameState() {
        return previousGameState;
    }

    public void setPreviousGameState(GameState previousGameState) {
        this.previousGameState = previousGameState;
    }

    public void step(GUI gui, Game game, long time) throws IOException {
        KeyStroke key = gui.getNextAction();
        controller.step(game,key,time);
        viewer.draw(gui, time);
    }

    public void updateState(GameState newState) throws IOException {
        if(newState == GameState.START){
            previousGameState = GameState.START;
        }
        else {
            previousGameState = currentGameState;
        }
        currentGameState = newState;
        StateActions();
    }

    public void Return() throws IOException {
        GameState aux = currentGameState;
        currentGameState = previousGameState;
        previousGameState = aux;
        StateActions();
    }

    public void StateActions() throws IOException {

        switch (currentGameState) {
            case START:
                StartMenu start = new StartMenu();
                controller = new StartMenuController(start);
                viewer = new StartMenuViewer(start);
                break;

            case PAUSE:
                PauseMenu pause = new PauseMenu();
                controller = new PauseMenuController(pause);
                viewer = new PauseMenuViewer(pause);
                SoundManager.getInstance().Pause();
                break;

            case NEW_GAME:
                ArenaBuilderByRound arenaBuilder;
                arenaBuilder = new ArenaBuilderByRound(1);
                this.arena = arenaBuilder.buildArena();
                controller = new ArenaController(arena);
                arenaController = (ArenaController) controller;
                viewer = new GameViewer(arena);
                break;

            case THEME:
                ThemeMenu theme = new ThemeMenu();
                controller = new ThemeMenuController(theme);
                viewer = new ThemeMenuViewer(theme);
                break;

            case SCOREBOARD:
                ScoreboardMenu scoreboard = new ScoreboardMenu();
                controller = new ScoreboardMenuController(scoreboard);
                viewer = new ScoreboardMenuViewer(scoreboard);
                break;

            case GAME_OVER:
                GameOverMenu over = new GameOverMenu(arena.getScore());
                controller = new GameOverMenuController(over);
                viewer = new GameOverMenuViewer(over);
                SoundManager.getInstance().Pause();
                break;

            case NEW_GAME_ROUND:
                ArenaBuilderByRound newArenaBuilder = new ArenaBuilderByRound(arena.getRound() + 1);
                int score = this.arena.getScore();
                this.arena = newArenaBuilder.buildArena();
                this.arena.increaseScore(score);
                controller = new ArenaController(arena);
                arenaController = (ArenaController) controller;
                viewer = new GameViewer(arena);
                break;

            case RESUME_GAME:
                controller = arenaController;
                viewer = new GameViewer(arena);
                if(arena.getFlyEnemy() != null){
                    SoundManager.getInstance().resumePlayingFlyEnemySound();
                }
                break;

            case INSTRUCTIONS:
                InstructionsMenu instructions = new InstructionsMenu();
                controller = new InstructionsMenuController(instructions);
                viewer = new InstructionsMenuViewer(instructions);
                break;
            case SOUND_OPTIONS:
                SoundOptionsMenu soundOptions = new SoundOptionsMenu();
                controller = new SoundOptionsMenuController(soundOptions);
                viewer = new SoundOptionsMenuViewer(soundOptions);
                break;

            case QUIT_GAME:
        }

    }
}
