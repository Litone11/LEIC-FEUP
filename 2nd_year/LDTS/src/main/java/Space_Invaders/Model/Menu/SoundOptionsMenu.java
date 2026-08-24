package Space_Invaders.Model.Menu;

import Space_Invaders.Controller.Sound.SoundManager;

import java.util.Arrays;

public class SoundOptionsMenu extends Menu {
    private boolean update;
    public SoundOptionsMenu() {
        options = Arrays.asList("mute all " + (SoundManager.getInstance().isMute() ? "ON" : "OFF"), "background music " + (SoundManager.getInstance().isMusic() ? "ON" : "OFF"), "sound effects "+ (SoundManager.getInstance().isEffects() ? "ON" : "OFF"),"exit");
        update = false;
    }

    public void updateOptions() {
        options = Arrays.asList("mute all " + (SoundManager.getInstance().isMute() ? "ON" : "OFF"), "background music " + (SoundManager.getInstance().isMusic() ? "ON" : "OFF"), "sound effects "+ (SoundManager.getInstance().isEffects() ? "ON" : "OFF"),"exit");
        update = true;
    }
    public boolean getUpdate() {return update;}
    public boolean isSelectedMuteAll(){return isSelected(0);}
    public boolean isSelectedBackgroundMusic(){return isSelected(1);}
    public boolean isSelectedSoundEffects(){return isSelected(2);}
    public boolean isSelectedExit(){return isSelected(3);}
}
