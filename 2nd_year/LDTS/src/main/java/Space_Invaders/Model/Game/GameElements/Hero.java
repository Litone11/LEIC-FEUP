package Space_Invaders.Model.Game.GameElements;


import Space_Invaders.Model.Position;

public class Hero extends AttackingSurvivalElement {

    private final int maxHealth;

    public Hero(Position position, int health, int damagePerShot){
        super(position,health,damagePerShot);
        this.maxHealth = 100;
    }


    public int getMaxHealth() {return maxHealth;}

    @Override
    public int getDamagePerShot(){
        return super.getDamagePerShot();
    }

    @Override
    public void decreaseHealth(int damage){
            this.setHealth(getHealth() - damage);
    }

    public void restoreHealth(){
        this.setHealth(maxHealth);
    }
}