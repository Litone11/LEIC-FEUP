package Space_Invaders.Controller.Sound;

import Space_Invaders.Model.Sound.Sound;
import Space_Invaders.Model.Sound.Sound_Options;
import Space_Invaders.State.Theme;

public class SoundManager {
    private Sound laser;
    private Sound dyingSound;
    private Sound switchOption;
    private Sound backgroundMusic;
    private Sound flyEnemyLowPitch;
    private Sound flyEnemyHighPitch;
    private Sound gameOver;
    private Sound enter;
    private boolean sound;
    private boolean music;
    private boolean effects;
    private static SoundManager soundManager;

    private SoundManager(){
            this.laser = Theme.getTheme().laser;
            this.dyingSound = Theme.getTheme().dyingSound;
            this.switchOption = Theme.getTheme().switchOption;
            this.backgroundMusic = Theme.getTheme().backgroundMusic;
            this.flyEnemyHighPitch = Theme.getTheme().flyEnemyHighPitch;
            this.flyEnemyLowPitch = Theme.getTheme().flyEnemyLowPitch;
            this.gameOver = Theme.getTheme().gameOver;
            this.enter = Theme.getTheme().enter;
            this.sound = false;
            this.music = true;
            this.effects = true;
            playSound(Sound_Options.MUSIC);
    }

    public static SoundManager getInstance(){
        if(soundManager == null){
            soundManager = new SoundManager();
        }
        return soundManager;
    }

    public void playSound(Sound_Options option){
        switch (option){
            case MUSIC :if(music) backgroundMusic.playContinuously();break;
            case LASER : if(effects) laser.play();break;
            case MENU_SWITCH : if(effects) switchOption.play();break;
            case DYING_SOUND : if(effects) dyingSound.play();break;
            case FLY_ENEMY_LOW : if(effects) flyEnemyLowPitch.playContinuously();break;
            case FLY_ENEMY_HIGH : if(effects) flyEnemyHighPitch.playContinuously();break;
            case GAME_OVER : if(effects) gameOver.play();break;
            case ENTER : if(effects) enter.play();break;
        }
    }

    public void stopSound(Sound_Options option){
        switch (option){
            case MUSIC -> backgroundMusic.stop();
            case LASER -> laser.stop();
            case MENU_SWITCH -> switchOption.stop();
            case DYING_SOUND -> dyingSound.stop();
            case FLY_ENEMY_HIGH -> flyEnemyHighPitch.stop();
            case FLY_ENEMY_LOW -> flyEnemyLowPitch.stop();
            case GAME_OVER -> gameOver.stop();
            case ENTER -> enter.stop();
        }
    }


    public void resumePlayingFlyEnemySound(){
        if(effects) {
            flyEnemyLowPitch.resumePlaying();
            flyEnemyHighPitch.resumePlaying();
        }
    }
    public void Pause(){
        laser.stop();
        switchOption.stop();
        dyingSound.stop();
        flyEnemyHighPitch.stop();
        flyEnemyLowPitch.stop();
    }

    private void updateLaser(){
        this.laser = Theme.getTheme().laser;
    }

    private void updateDyingSound() {
        this.dyingSound = Theme.getTheme().dyingSound;
    }

    private void updateSwitchOption() {
        this.switchOption = Theme.getTheme().switchOption;
    }

    private void updateBackgroundMusic() {
        this.backgroundMusic.stop();
        this.backgroundMusic = Theme.getTheme().backgroundMusic;
        playSound(Sound_Options.MUSIC);
    }


    private void updateFlyEnemyLowPitch() {
        this.flyEnemyLowPitch = Theme.getTheme().flyEnemyLowPitch;
    }

    private void updateFlyEnemyHighPitch() {
        this.flyEnemyHighPitch = Theme.getTheme().flyEnemyHighPitch;
    }

    private void updateGameOver() {
        this.gameOver = Theme.getTheme().gameOver;
    }
    private void updateEnter() {
        this.enter = Theme.getTheme().enter;
    }

    public void updateSounds(){
        updateLaser();
        updateDyingSound();
        updateSwitchOption();
        updateBackgroundMusic();
        updateFlyEnemyHighPitch();
        updateFlyEnemyLowPitch();
        updateGameOver();
        updateEnter();
    }

    public boolean isMute() {
        return sound;
    }

    public void setMute(boolean sound) {
        this.sound = sound;
        this.effects = !sound;
        this.music = !sound;
        if(sound)stopSound(Sound_Options.MUSIC);
        else backgroundMusic.resumePlaying();

    }

    public void updateMute(boolean sound){
        this.sound = sound;
    }

    public boolean isMusic() {
        return music;
    }

    public void setMusic(boolean music) {
        this.music = music;
        if(music) backgroundMusic.resumePlaying();
        else stopSound(Sound_Options.MUSIC);
    }

    public boolean isEffects() {
        return effects;
    }

    public void setEffects(boolean effects) {
        this.effects = effects;
    }
}

