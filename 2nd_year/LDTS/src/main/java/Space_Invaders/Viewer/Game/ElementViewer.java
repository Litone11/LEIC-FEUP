package Space_Invaders.Viewer.Game;

import Space_Invaders.GUI.GUI;
import Space_Invaders.Model.Game.Element;

public interface ElementViewer<T extends Element> {
    void draw(GUI gui, T element);

}
