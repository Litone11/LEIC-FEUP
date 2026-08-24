package Space_Invaders.Viewer.Menu;


import Space_Invaders.GUI.GUI;
import Space_Invaders.Model.Menu.Menu;
import Space_Invaders.Model.Position;
import Space_Invaders.Viewer.Viewer;
import Space_Invaders.State.Theme;

public abstract class MenuViewer<T extends Menu> extends Viewer<T> {

    private final int reference_x;
    private final int reference_y;
    public MenuViewer(T menu, Position position){
        super(menu);
        reference_x = position.getX();
        reference_y = position.getY();
    }


    protected void drawOptions(GUI gui){
        for(int i = 0; i < getModel().getNumberOfOptions(); i++){
            if(getModel().isSelected(i)) {
                gui.drawText(new Position(reference_x, reference_y  + 5 * i), ">" + getModel().getOption(i), Theme.getTheme().menuColorSelected);
            }
            else{
                gui.drawText(new Position(reference_x, reference_y + 5 * i), getModel().getOption(i), Theme.getTheme().menuColor);
            }
        }
    }

    protected void drawMenuTitle(GUI gui, String title, String color, Position position){
        gui.drawText(position,title, Theme.getTheme().menuColorTitle2);
    }

    protected int getReference_x(){
        return reference_x;
    }

    protected int getReference_y(){
        return reference_y;
    }
}
