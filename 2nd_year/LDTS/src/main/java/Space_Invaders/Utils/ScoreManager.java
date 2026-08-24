package Space_Invaders.Utils; // Define a pasta adequada

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

public class ScoreManager {
    private static final List<Integer> scores = new ArrayList<>();

    public static void addScore(int score) {
        scores.add(score);
    }

    public static List<Integer> getScores() {
        return Collections.unmodifiableList(scores);
    }

    public static int getHighestScore() {
        return scores.stream().max(Integer::compare).orElse(0);
    }

    public static void resetScores() {
        scores.clear();
    }
}
