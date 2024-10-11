export default {
    data() {
        return {
            passtxt: '',
            safe_param_bar_width: 0,
        }
    },
    computed: {
        password_strength() {
            return safe_param(this.passtxt);
        },
    },
    watch: {
        password_strength(new_strength) {
            this.safe_param_bar_width = this.calculate_bar_width(new_strength);
        }
    },
    methods: {
        safe_param(passtxt) {
            let score = 0;
            // 長さによる基本スコア
            if (passtxt.length >= 12) score += 6;
            else if (passtxt.length >= 10) score += 4;
            else if (passtxt.length >= 8) score += 2;
            else if (passtxt.length >= 6) score += 1;
            else score -= 1; // 6文字未満は減点
            // 文字種類の多様性
            if (/[a-z]/.test(passtxt)) score += 1; // 小文字
            if (/[A-Z]/.test(passtxt)) score += 1; // 大文字
            if (/\d/.test(passtxt)) score += 1;    // 数字
            if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(passtxt)) score += 1; // 特殊文字
            // 連続した文字の判定
            let consecutive = 0;
            for (let i = 0; i < passtxt.length - 1; i++) {
                if (passtxt.charCodeAt(i) + 1 === passtxt.charCodeAt(i + 1)) {
                    consecutive++;
                } else {
                    consecutive = 0;
                }
                if (consecutive >= 2) {
                    score -= 1;
                    break;
                }
            }
            // 同一文字の連続判定
            let repeated = 0;
            for (let i = 0; i < passtxt.length - 1; i++) {
                if (passtxt[i] === passtxt[i + 1]) {
                    repeated++;
                } else {
                    repeated = 0;
                }
                if (repeated >= 2) {
                    score -= 1;
                    break;
                }
            }
            // 最終スコアの調整（0-10の範囲に収める）
            score = Math.max(0, Math.min(10, score));
        },
        calculate_bar_width(score) {
            // スコアからバーの幅を計算
            return (score * 10);
        }
    }
}