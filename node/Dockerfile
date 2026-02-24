FROM node:18-alpine

WORKDIR /app

# Копируем package.json и package-lock.json
COPY package*.json ./

# Устанавливаем только production-зависимости
RUN npm ci --only=production

# Копируем исходный код
COPY src/ ./src/
COPY plugins/ ./plugins/

# Открываем порт, на котором работает приложение
EXPOSE 3000

# Запуск
CMD ["node", "src/index.js"]
