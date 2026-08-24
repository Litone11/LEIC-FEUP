package Space_Invaders.Model.Menu;

import java.util.List;

public abstract class Menu {
    protected List<String> options;
    protected int selected = 0;

    public void next(){
        selected++;
        if(options.size() <= selected){
            selected = 0;
        }
    }

    public void previous(){
        selected--;
        if(selected < 0){
            selected = options.size() - 1;
        }
    }

    public String getOption(int i){ return options.get(i); }
    public boolean isSelected(int i){ return selected == i; }
    public int getNumberOfOptions() { return options.size(); }


}
