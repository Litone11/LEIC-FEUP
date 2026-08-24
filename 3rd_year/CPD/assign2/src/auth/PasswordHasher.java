package auth;

import javax.crypto.SecretKeyFactory;
import javax.crypto.spec.PBEKeySpec;
import java.security.SecureRandom;
import java.util.Base64;

public final class PasswordHasher {
    private static final String ALGORITHM = "PBKDF2WithHmacSHA256";
    private static final int ITERATIONS = 120_000;
    private static final int KEY_LENGTH = 256;
    private static final int SALT_BYTES = 16;

    private static final SecureRandom RNG = new SecureRandom();

    private PasswordHasher() {
    }

    public record Hashed(String saltB64, String hashB64) {
    }

    public static Hashed hash(String password) {
        byte[] salt = new byte[SALT_BYTES];
        RNG.nextBytes(salt);
        return new Hashed(encode(salt), encode(derive(password, salt)));
    }

    public static boolean verify(String password, String saltB64, String hashB64) {
        byte[] salt = decode(saltB64);
        byte[] expected = decode(hashB64);
        byte[] actual = derive(password, salt);
        return constantTimeEquals(expected, actual);
    }

    private static byte[] derive(String password, byte[] salt) {
        try {
            PBEKeySpec spec = new PBEKeySpec(password.toCharArray(), salt, ITERATIONS, KEY_LENGTH);
            SecretKeyFactory factory = SecretKeyFactory.getInstance(ALGORITHM);
            byte[] out = factory.generateSecret(spec).getEncoded();
            spec.clearPassword();
            return out;
        } catch (Exception e) {
            throw new IllegalStateException("PBKDF2 unavailable", e);
        }
    }

    private static boolean constantTimeEquals(byte[] a, byte[] b) {
        if (a.length != b.length) {
            return false;
        }
        int diff = 0;
        for (int i = 0; i < a.length; i++) {
            diff |= a[i] ^ b[i];
        }
        return diff == 0;
    }

    private static String encode(byte[] data) {
        return Base64.getEncoder().withoutPadding().encodeToString(data);
    }

    private static byte[] decode(String value) {
        return Base64.getDecoder().decode(value);
    }
}
