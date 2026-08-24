package Space_Invaders.Model.Game.GameElements;

import Space_Invaders.Model.Game.Element;
import Space_Invaders.Model.Position;

public class SurvivalElement extends Element {

    private int health;

    public SurvivalElement(Position position, int health) {
        super(position);
        this.health = health;
    }

    public int getHealth() {
        return health;
    }

    public void setHealth(int health){ this.health = health; }

    public void decreaseHealth(int damage){
        health-=damage;
    }

    public boolean isDestroyed(){
        return health <= 0;
    }


    @Override
    public boolean equals(Object o){
        if(this == o){
            return true;
        }
        if(!(o instanceof SurvivalElement)){
            return false;
        }
        return this.getPosition().equals(((SurvivalElement) o).getPosition()) && this.health == ((SurvivalElement) o).getHealth();
    }

    @Override
    public int hashCode() {
        int prime = 31;
        int result = prime + health;
        result = prime * result + getPosition().hashCode();
        return result;
    }


}

