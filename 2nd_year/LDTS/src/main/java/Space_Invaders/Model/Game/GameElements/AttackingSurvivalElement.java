package Space_Invaders.Model.Game.GameElements;

import Space_Invaders.Model.Position;

public class AttackingSurvivalElement extends SurvivalElement {

    private int damagePerShot;

    public AttackingSurvivalElement(Position position, int health, int damagePerShot) {
        super(position, health);
        this.damagePerShot = damagePerShot;
    }

    public int getDamagePerShot() {return damagePerShot;}

    public void setDamagePerShot(int damagePerShot){this.damagePerShot = damagePerShot;}

    @Override
    public boolean equals(Object o){
        if(this == o){
            return true;
        }
        if(!(o instanceof AttackingSurvivalElement)){
            return false;
        }
        return this.getPosition().equals(((AttackingSurvivalElement) o).getPosition()) && this.getHealth() == ((AttackingSurvivalElement) o).getHealth() && this.getDamagePerShot() == ((AttackingSurvivalElement) o).getDamagePerShot();
    }

    @Override
    public int hashCode() {
        int prime = 31;
        int result = prime + damagePerShot;
        result = prime * result + getHealth();
        result = prime * result + getPosition().hashCode();
        return result;
    }
}