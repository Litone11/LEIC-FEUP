package Space_Invaders;

import Space_Invaders.GUI.GUI;
import Space_Invaders.GUI.GUILanterna;
import Space_Invaders.State.GameState;
import Space_Invaders.State.State;
import Space_Invaders.State.ThemeState;
import Space_Invaders.State.Theme;

import java.awt.*;
import java.io.IOException;
import java.net.URISyntaxException;
import java.util.ArrayList;
import java.util.List;

public class Game {
    private State state;
    private GUI gui;
    private Theme theme;

    private Game() throws IOException, URISyntaxException, FontFormatException{
        this.gui = new GUILanterna(59, 44);
        this.state = State.getInstance();
        this.theme = Theme.getTheme();
    }


    public State getState() {
        return state;
    }

    public void setState(GameState gameState) throws IOException {
        state.updateState(gameState);
    }

    public void setTheme(ThemeState themestate) throws IOException{
        theme.updateTheme(themestate);
    }

    public void setPreviousState() throws IOException {
        state.Return();
    }

    public GUI getGui(){return gui;}

    private void startGame() throws IOException, InterruptedException {
        int FPS = 30;
        int frameTime = 1000 / FPS;
        while(this.state.getCurrentGameState() != GameState.QUIT_GAME){
            long startTime = System.currentTimeMillis();
            state.step(gui,this,startTime);
            long elapsedTime = System.currentTimeMillis() - startTime;
            long sleepTime = frameTime - elapsedTime;
            try {
                if (sleepTime > 0) Thread.sleep(sleepTime);
            } catch (InterruptedException e) {
                throw e;
            }
        }
        gui.close();
    }

    public static void main(String[] args) throws IOException, URISyntaxException, FontFormatException, InterruptedException {
        Game game = new Game();
        game.startGame();
    }
}
