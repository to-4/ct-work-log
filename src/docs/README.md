
---

## 🖊 命名規約

- ファイル名は以下のパターンを推奨  
  - ユースケース: `UC-<領域>-<説明>_v<番号>.drawio`  
    例: `UC-auth-login_v1.drawio`
  - ER: `ER-<コンテキスト名>_v<番号>.drawio`  
    例: `ER-core_v3.drawio`
- `<番号>` は **破壊的変更時**にカウントアップ
- バージョンは図内フッターにも記載する

---

## 🎨 図作成ルール

- 必ず `templates/` の雛形から複製して作成
- 凡例・色・線のルールはテンプレのまま変更しない
- **ユースケース図**  
  - アクター：灰色スティックマン  
  - ユースケース：薄青楕円  
  - システム境界：黒枠・角丸矩形  
- **ER 図**  
  - PK：黄色背景（#FFF7D1）太字  
  - FK：薄オレンジ背景（#FFE7CC）斜体  
  - 1..* などのカーディナリティはテキストで明記

---

## 📤 エクスポート運用

- レビューやドキュメント用に `exports/` に出力
- 形式は **SVG推奨**（GitHub上で差分確認可能）
- PNG/PDF は必要な場合のみ出力

---

## 🔄 更新手順

1. `templates/` から新規図を複製  
2. `diagrams/` の該当サブフォルダに配置  
3. 図内フッターの **Version / Date / Author** を更新  
4. 必要に応じて `exports/` にエクスポート  
5. 関連コードやマイグレーションとあわせて PR 作成

---

## ✅ レビュー観点

- 凡例・配色・線種が統一されているか
- ファイル名・フッターのバージョンが最新か
- 図の内容とコードが一致しているか
- 不要な詳細や未使用要素が含まれていないか

---

## 🛠 使用ツール

- [Draw.io Integration for VS Code](https://marketplace.visualstudio.com/items?itemName=hediet.vscode-drawio)
- または [draw.io (diagrams.net)](https://app.diagrams.net/)

---